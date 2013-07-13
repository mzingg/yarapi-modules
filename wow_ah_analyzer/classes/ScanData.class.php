<?php
/**
 * @Entity
 * @Table(indexes={
 *   @index(name="item_idx", columns={"item"}),
 *   @index(name="bid_idx", columns={"bid"}),
 *   @index(name="buyout_idx", columns={"buyout"}),
 *   @index(name="time_left_idx", columns={"time_left"})
 * })
 **/
class ScanData
{
	/** @Id @Column(columnDefinition="bigint unsigned") **/
	protected $id;
	
	/**
	 * @Id @ManyToOne(targetEntity="ScanRequest")
	 **/
	protected $scan_request;
	
	/** @Column(length=12) **/
	protected $type;
	
	/** @Column(columnDefinition="integer unsigned") **/
	protected $item;
	
	/** @Column(length=12) **/
	protected $owner;
	
	/** @Column(columnDefinition="integer unsigned") **/	
	protected $bid;
	
	/** @Column(columnDefinition="integer unsigned") **/
	protected $buyout;
	
	/** @Column(columnDefinition="tinyint unsigned") **/
	protected $quantity;
	
	/** @Column(length=12) **/
	protected $time_left;
	
	public function setScanRequest(ScanRequest $oScanRequest) {
		$this->scan_request = $oScanRequest;
	}

	public static function create($aData) {		
		$oResult = new ScanData();
		
		$oResult->id = $aData['auc'];
		$oResult->type = $aData['type'];
		$oResult->item = $aData['item'];
		$oResult->owner = $aData['owner'];
		$oResult->bid = $aData['bid'];
		$oResult->buyout = $aData['buyout'];
		$oResult->quantity = $aData['quantity'];
		$oResult->time_left = $aData['timeLeft'];

		return $oResult;
	}
}