<?php
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(indexes={@index(name="timestamp_idx", columns={"timestamp"}),})
 **/
class ScanRequest
{
	/** @Id @GeneratedValue @Column(columnDefinition="integer unsigned AUTO_INCREMENT") **/
	protected $id;
	
	/** @Column(columnDefinition="char(32)", unique=true) **/
	protected $checksum;
	
	/** @Column(columnDefinition="integer unsigned") **/
	protected $timestamp;
	
	/** @Column(columnDefinition="integer unsigned") **/
	protected $created;
	
	public function __construct() {
		$this->data = new ArrayCollection();
	}
	
	public static function create($aData) {		
		$nNow = time();
		
		$oResult = new ScanRequest();
		
		$oResult->timestamp = $aData['timestamp'];
		$oResult->checksum = $aData['checksum'];
		
		$oResult->created = $nNow;
		
		return $oResult;
	}
	
	public function getChecksum() {
		return $this->checksum;
	}
}