<?

function wow_ah_analyzer_cmd_fetchah($aArguments) {
	$oModule = Modules::findModuleByName('wow_ah_analyzer');
	$oDoctrineModule = new DoctrineModule($oModule);
	
	$oEntityManager = $oDoctrineModule->getEntityManager();
	
	$nFilesImported = ScanHelper::synchronizeScans($oEntityManager);
	return "Imported $nFilesImported files";
}
