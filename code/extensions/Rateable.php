<?php
/**
 * @author Shea Dawson <shea@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class Rateable extends DataExtension {

	private static $db = array(
		'EnableRatings' => 'Boolean'
	);


	private static $defaults = array(
		'EnableRatings' => 1
	);


	private static $dependencies = array(
		'rateableService'	=> '%$RateableService',
	);

	
	/**
	 * @var RateableService
	 */
	public $rateableService;


	/**
	 * @var String
	 */
	private $htmlIdPostfix;


	public function updateSettingsFields(FieldList $fields){
		$fields->addFieldToTab('Root.Settings', new CheckboxField('EnableRatings', 'Enable Ratings'));
	}


	public function updateCMSFields(FieldList $fields){
		if(!is_subclass_of($this->owner, 'SiteTree')){
			$fields->addFieldToTab('Root.Main', new CheckboxField('EnableRatings', 'Enable Ratings'));	
		}
	}


	/**
	 * gets the average rating score
	 * @return Int
	 **/
	public function getAverageScore(){
		return $this->rateableService->getRatingsFor($this->owner->ClassName, $this->owner->ID)->avg('Score');
	}


	/**
	 * gets the number of ratings
	 * @return int
	 */
	public function getNumberOfRatings() {
		return $this->rateableService->getRatingsFor($this->owner->ClassName, $this->owner->ID)->count();
	}

	/**
	 * checks to see if the current user has rated this object
	 * by checking against the rating SessionID and MemberID
	 * @return Boolean
	 **/
	public function UserHasRated(){
		return $this->rateableService->userHasRated($this->owner->ClassName, $this->owner->ID);
	}


	/**
	 * returns the JS and HTML required for the star rating UI
	 * @var $htmlIdPostfix String - appends a given unique identifier to the ratingHTMLID. This allows 
	 * multiple instances of the same ratable object on one page
	 * @return String
	 **/
	public function RateableUI($htmlIdPostfix = false){
		if(!$this->owner->EnableRatings) return;

		$this->htmlIdPostfix = $htmlIdPostfix;

		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(RATEABLE_MODULE . '/javascript/jquery.raty.js');
		Requirements::javascript(RATEABLE_MODULE . '/javascript/rateable.js');

		return $this->owner->renderWith('RateableUI');
	}


	/**
	 * returns a unique HTML ID for each RateableUI div
	 * @return String
	 **/
	public function getRatingHTMLID(){
		$parts = array(
			$this->owner->ClassName,
			$this->owner->ID,
			'rating'
		);

		if($this->htmlIdPostfix){
			$parts[] = $this->htmlIdPostfix;
		}

		return implode('-', $parts);
	}


	/**
	 * returns a string to be used in the RatableUI's css class attribute
	 * @return String
	 **/
	public function getRatingCSSClass(){
		$parts = array(
			'rateable-ui'
		);

		if($this->UserHasRated()){
			$parts[] = 'disabled';
		}

		return implode(' ', $parts);
	}
	

	/**
	 * return the url path for rating this object
	 * @return String
	 **/
	public function RatePath(){
		return Controller::join_links(RateableController::URLSegment, 'rate', $this->owner->ClassName, $this->owner->ID);
	}
}
