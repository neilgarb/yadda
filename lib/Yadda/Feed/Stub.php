<?php

class Yadda_Feed_Stub {
	protected $_guid = null;
	protected $_link = null;
	protected $_title = null;
	protected $_description = null;
	protected $_date = null;
	protected $_price = null;
	protected $_value = null;
	protected $_discount = null;
	protected $_geo = null;
	protected $_images = array();
	
	public function __construct() {
		$this->_date = date('Y-m-d H:i:s');
	}
	
	/**
	 * Sets this stub's GUID.
	 * 
	 * @param string $guid
	 * @return Yadda_Feed_Stub
	 */
	public function setGuid($guid) {
		$this->_guid = $guid;
		return $this;
	}
	
	/**
	 * Returns this stub's GUID, or null if not set.
	 * 
	 * @return string|null
	 */
	public function getGuid() {
		return $this->_guid;
	}
	
	/**
	 * Sets this stub's link.
	 * 
	 * @param string $link
	 * @return Yadda_Feed_Stub
	 */
	public function setLink($link) {
		$this->_link = $link;
		return $this;
	}
	
	/**
	 * Returns this stub's link, or null if not set.
	 * 
	 * @return string|null
	 */
	public function getLink() {
		return $this->_link;
	}
	
	/**
	 * Sets this stub's title.
	 * 
	 * @param string $title
	 * @return Yadda_Feed_Stub
	 */
	public function setTitle($title) {
		$this->_title = $title;
		return $this;
	}
	
	/**
	 * Returns this stub's title, or null if not set.
	 * 
	 * @return string|null
	 */
	public function getTitle() {
		return $this->_title;
	}
	
	/**
	 * Sets this stub's description.
	 * 
	 * @param string $description
	 * @return Yadda_Feed_Stub
	 */
	public function setDescription($description) {
		$this->_description = $description;
		return $this;
	}
	
	/**
	 * Returns this stub's description, or null if not set.
	 * 
	 * @return string|null
	 */
	public function getDescription() {
		return $this->_description;
	}
	
	/**
	 * Sets this stub's date.
	 * 
	 * @param string $date YYYY-MM-DD HH:MM:SS format
	 * @return Yadda_Feed_Stub
	 */
	public function setDate($date) {
		$this->_date = $date;
		return $this;
	}
	
	/**
	 * Returns this stub's date in YYYY-MM-DD HH:MM:SS format.
	 * 
	 * @return string
	 */
	public function getDate() {
		return $this->_date;
	}
	
	/**
	 * Sets this stub's price.
	 * 
	 * @param float $price
	 * @return Yadda_Feed_Stub
	 */
	public function setPrice($price) {
		$this->_price = $price;
		return $this;
	}
	
	/**
	 * Returns this stub's price, or null if not set.
	 * 
	 * @return float|null
	 */
	public function getPrice() {
		return $this->_price;
	}
	
	/**
	 * Sets this stub's Rand value.
	 * 
	 * @param float $value
	 * @return Yadda_Feed_Stub
	 * @throws Yadda_Feed_Exception
	 */
	public function setValue($value) {
		$value = (float) $value;
		if ($value <= 0) {
			throw new Yadda_Feed_Exception('Invalid deal value "'.$value.'".');
		}
		$this->_value = $value;
		return $this;
	}
	
	/**
	 * Returns this stub's Rand value, or null if not set.
	 * 
	 * @return float|null
	 */
	public function getValue() {
		return $this->_value;
	}
	
	/**
	 * Sets this stub's percentage discount.
	 * 
	 * @param float $discount
	 * @return Yadda_Feed_Stub
	 * @throws Yadda_Feed_Exception
	 */
	public function setDiscount($discount) {
		$discount = (float) $discount;
		if ($discount < 0 || $discount > 100) {
			throw new Yadda_Feed_Exception('Invalid discount percentage "'.$discount.'".');
		}
		$this->_discount = $discount;
		return $this;
	}
	
	/**
	 * Returns this stub's percentage discount, or null if not set.
	 * 
	 * @return float|null
	 */
	public function getDiscount() {
		return $this->_discount;
	}
	
	/**
	 * Sets this stub's coordinates.
	 * 
	 * @param array $geo Tuple of (latitude, longitude)
	 * @return Yadda_Feed_Stub
	 * @throws Yadda_Feed_Exception
	 */
	public function setGeo(array $geo) {
		if (
			sizeof($geo) == 2 &&
			(is_float($geo[0]) || is_int($geo[0])) &&
			$geo[0] > -180 &&
			$geo[0] < 180 &&
			(is_float($geo[1]) || is_int($geo[1])) &&
			$geo[1] > -180 &&
			$geo[1] < 180
		) {
			$this->_geo = $geo;
		} else {
			throw new Yadda_Feed_Exception('Invalid geo coords.');
		}
		return $this;
	}
	
	/**
	 * Returns this stub's geo coords, or null if not set.
	 * 
	 * @return array|null
	 */
	public function getGeo() {
		return $this->_geo;
	}
	
	/**
	 * Adds an image URL to this stub.
	 * 
	 * @param string $image
	 * @return Yadda_Feed_Stub
	 */
	public function addImage($image) {
		$this->_images[] = $image;
		return $this;
	}
	
	/**
	 * Returns the image URLs associated with this stub.
	 * 
	 * @return array
	 */
	public function getImages() {
		return $this->_images;
	}
}