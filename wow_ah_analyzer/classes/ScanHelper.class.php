<?php
use Doctrine\ORM\Query\AST\ExistsExpression;

use Doctrine\ORM\EntityManager;

class ScanHelper {

	public static function synchronizeScans(EntityManager $oEntityManager) {

		$aExistingScans = array();
		self::_loadExistingScans($oEntityManager, $aExistingScans);
		return self::_readImportDirectory($oEntityManager, $aExistingScans);
	}

	private static function _loadExistingScans(EntityManager $oEntityManager, & $aExistingScans) {
		foreach ($oEntityManager->getRepository('ScanRequest')->findAll() as $oScanRequest) {
			$sChecksum = $oScanRequest->getChecksum();
				
			if (!$sChecksum)
				continue;

			$aExistingScans[$sChecksum] = $oScanRequest;
		}
	}

	private static function _readImportDirectory(EntityManager $oEntityManager, & $aExistingScans) {
		$sDirectoryPath = InstallationState::getInstance()->getVarDirectoryPath() . '/import';
		if (!is_dir($sDirectoryPath) || !is_readable($sDirectoryPath)) {
			yarapi_log(sprintf('Could not read import directory [%s]', $sDirectoryPath), PEAR_LOG_ERR);
			return;
		}
			
		$nCounter = 0;
		if ($oDirectoryHandle = opendir($sDirectoryPath)) {
			while (false !== ($sFilename = readdir($oDirectoryHandle))) {
				if (strpos($sFilename, '.bz2') === false)
					continue;

				$sFullFilePath = $sDirectoryPath . '/' . $sFilename;
				list($sFileContent, $sChecksum) = self::_readScanFile($sFullFilePath);
				if (!$sChecksum)
					continue;

				$nFileTimestamp = filemtime($sFullFilePath);

				if (array_key_exists($sChecksum, $aExistingScans)) {
					yarapi_log(sprintf('Skipped file [%s] (already imported).', $sFilename));
					continue;
				}

				$oScanRequest = ScanHelper::_createScanRequest($oEntityManager, $sFileContent, $sChecksum, $nFileTimestamp);
				if (!$oScanRequest)
					continue;
				
				$oEntityManager->flush();
				$oEntityManager->clear();
				
				$aExistingScans[$oScanRequest->getChecksum()] = true;
				yarapi_log(sprintf('Imported file [%s].', $sFilename));
				$nCounter++;						

			}
			closedir($oDirectoryHandle);
		}
		
		return $nCounter;
	}

	private static function _readScanFile($sFilename) {
		if (!($oBzHandle = bzopen($sFilename, "r")))
			return array('', false);

		$sFileContents = '';
		while (!feof($oBzHandle)) {
			$sFileContents .= bzread($oBzHandle, 4096);
		}
		bzclose($oBzHandle);

		$sChecksum = md5($sFileContents);

		return array($sFileContents, $sChecksum);
	}

	static function _createScanRequest(EntityManager $oEntityManager, $sFileContent, $sChecksum, $nTimestamp) {
		$oResult = ScanRequest::create(array('timestamp' => $nTimestamp, 'checksum' => $sChecksum));
		$oEntityManager->persist($oResult);
		$oEntityManager->flush($oResult);

		if (!self::_fetchData($oEntityManager, $oResult, $sFileContent)) {
			yarapi_log('Could not load auction data');
			return false;
		}
		
		return $oResult;
	}

	private static function _fetchData(EntityManager $oEntityManager, ScanRequest $oScanRequest, $sFileContents) {
		$aApiResult = json_decode($sFileContents, true);
		if (!$aApiResult) {
			yarapi_log("Invalid API result", PEAR_LOG_ERR);
			yarapi_debug($aApiResult);

			return false;
		}

		foreach ($aApiResult as $sType => $aAuctionData) {
			if (!array_key_exists('auctions', $aAuctionData))
				continue;

			foreach ($aAuctionData['auctions'] as $aDataEntry) {
				$aDataEntry['type'] = $sType;
				if ($oScanData = ScanData::create($aDataEntry)) {
					$oScanData->setScanRequest($oScanRequest);
					$oEntityManager->persist($oScanData);
				}
			}
		}

		return true;
	}
}
