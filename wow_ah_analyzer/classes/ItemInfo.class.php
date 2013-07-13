<?php
/** @Entity **/
class ItemInfo {
	
	/** @Id @Column(columnDefinition="integer unsigned") **/
	protected $id;
	
	/** @Column() **/
	protected $icon;
}