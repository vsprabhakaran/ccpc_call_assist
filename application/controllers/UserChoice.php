<?php
class UserChoice extends CI_Controller {
	public function show() {
		$this -> load -> model('banner_Model');
		$this -> load -> view('template/headtag', array("title" => "CCPC Calling Process"));
		$CircleNames = $this -> banner_Model -> getCircleNames();
		$choicePageData = array('CircleNames' => $CircleNames);

		$CircleNames = $this -> banner_Model -> getCircleNames();
		$circleStatus;
		foreach ($CircleNames as $cur_circle) {
			$circleStatus["all_circle_names"][] = $cur_circle;
			$circleStatus["$cur_circle"]['total'] = $this -> banner_Model -> getCircle_TotalChequeNumber($cur_circle, date("Y-m-d"));
			$circleStatus["$cur_circle"]['confirmed'] = $this -> banner_Model -> getCircle_ConfirmedChequeNumber($cur_circle, date("Y-m-d"));
		}
		$circleStripData['circleStatus'] = $circleStatus;
		$isBannerLoaded = TRUE;

		$this -> load -> view('template/statusBanner', $circleStripData);
		$this -> load -> view('choicepanel', $choicePageData);

	}

	public function processChoice() {
		$this -> load -> library('session');
		$chosen_date = $this -> input -> post('datepicker');
		$chosen_circle = $this -> input -> post('circleName');
		if ($chosen_circle === FALSE) {
			$this -> show();
			return;
		}
		$chosen_date = date("Y-m-d", strtotime($chosen_date));
		echo $chosen_date;
		$this -> session -> set_userdata('ccpc_calling_chequeDate', $chosen_date);
		$this -> session -> set_userdata('ccpc_calling_circleName', $chosen_circle);
		$this -> session -> set_userdata('ccpc_calling_pfIndex', '6716547');
		redirect('/ProcessEntry/index');
		/*$this -> session -> set_userdata('ccpc_calling_chequeDate', '2015-03-19');
		 $this -> session -> set_userdata('ccpc_calling_circleName', 'West Bengal');*/
	}

}
