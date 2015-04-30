<?php
class ProcessEntry extends CI_Controller {

	//Public data variables
	private $m_pfIndex;
	private $m_currentDate;
	private $m_currentCircle;

	public function __construct() {
		parent::__construct();
		$this -> load -> library('session');
		//Delete the setting of session data once the previous page is completely implemen`ted.
		/*$this -> session -> set_userdata('ccpc_calling_pfIndex', '6716547');
		 $this -> session -> set_userdata('ccpc_calling_chequeDate', '2015-03-19');
		 $this -> session -> set_userdata('ccpc_calling_circleName', 'West Bengal');*/
		if ($this -> session -> has_userdata('ccpc_calling_pfIndex')) {
			$this -> m_pfIndex = $this -> session -> userdata('ccpc_calling_pfIndex');
			$this -> m_currentDate = $this -> session -> userdata('ccpc_calling_chequeDate');
			$this -> m_currentCircle = $this -> session -> userdata('ccpc_calling_circleName');
		} else {
			redirect('userchoice/show');
		}
	}

	public function index() {

		$this -> load -> helper('form');
		$this -> load -> model('banner_Model');
		$this -> load -> model('chequeDetails_Model');
		$this -> load -> model('statusTable_Model');

		$this -> displayBanner();

		$lockedCheques = $this -> statusTable_Model -> getLockedChequeByPF($this -> m_pfIndex, $this -> m_currentDate);

		//When the number of locked cheques is more than zero, it means that user is already viewing a set of cheques and we have to reproduce those cheques again.
		if (count($lockedCheques) > 0) {
			foreach ($lockedCheques as $chequeIter) {//Adding amount and name for cheques
				$chequeIter -> AMOUNT = $this -> chequeDetails_Model -> getAmountOfCheque($chequeIter -> MICRCODE, $chequeIter -> CHEQUENO, $chequeIter -> TC, $chequeIter -> ACCOUNTNO, $chequeIter -> CHEQUEDATE);
				$chequeIter -> NAME = $this -> chequeDetails_Model -> getBenificiaryNameOfCheque($chequeIter -> MICRCODE, $chequeIter -> CHEQUENO, $chequeIter -> TC, $chequeIter -> ACCOUNTNO, $chequeIter -> CHEQUEDATE);
			}
			$branchCode = $this -> chequeDetails_Model -> getBranchCodeByAccountNumber($lockedCheques[0] -> ACCOUNTNO);
			$branchName = $this -> chequeDetails_Model -> getBranchNameByAccountNumber($lockedCheques[0] -> ACCOUNTNO);
			$lockedChequesData = array('chequeCategory' => 'lockedCheques', 'chequeSet' => $lockedCheques, 'branchName' => $branchName, 'branchCode' => $branchCode);
			$this -> load -> view('chequePanel', $lockedChequesData);
		} elseif ($newAccountNumber = $this -> chequeDetails_Model -> findNext_HVTChequeSet_AccountNumber($this -> m_currentCircle, $this -> m_currentDate)) {//This block has to serve a new set of cheques for this user
			$newChequeSet = $this -> chequeDetails_Model -> getChequeSet_For_AccountNumber($newAccountNumber, $this -> m_currentDate);
			$statusTableUpdateSuccess = $this -> chequeDetails_Model -> insertInto_StatusTable_From_ICTable($newChequeSet, $this -> m_pfIndex);
			$branchCode = $this -> chequeDetails_Model -> getBranchCodeByAccountNumber($newChequeSet[0] -> ACCOUNTNO);
			$branchName = $this -> chequeDetails_Model -> getBranchNameByAccountNumber($newChequeSet[0] -> ACCOUNTNO);
			$newChequesData = array('chequeCategory' => 'newCheques', 'chequeSet' => $newChequeSet, 'branchName' => $branchName, 'branchCode' => $branchCode);
			$this -> load -> view('chequePanel', $newChequesData);
		} else {//Code to display that there are no more cheques to process
			$this -> displayNoMoreData("New Cheques", "No Cheques Found!!!");
		}
	}

	public function displayBanner() {
		$this -> load -> model('banner_Model');
		$isBannerLoaded = FALSE;
		if ($this -> m_pfIndex != NULL && $this -> m_currentDate != NULL) {
			$completedNumber = $this -> banner_Model -> get_completed_status($this -> m_pfIndex, $this -> m_currentDate);
			$pendingNumber = $this -> banner_Model -> get_pending_status($this -> m_pfIndex, $this -> m_currentDate);
			$CircleNames = $this -> banner_Model -> getCircleNames();
			$circleStatus;
			foreach ($CircleNames as $cur_circle) {
				$circleStatus["all_circle_names"][] = $cur_circle;
				$circleStatus["$cur_circle"]['total'] = $this -> banner_Model -> getCircle_TotalChequeNumber($cur_circle, $this -> m_currentDate);
				$circleStatus["$cur_circle"]['confirmed'] = $this -> banner_Model -> getCircle_ConfirmedChequeNumber($cur_circle, $this -> m_currentDate);
			}
			$userStripData = array('login_pf' => $this -> m_pfIndex, 'chosen_date' => $this -> m_currentDate, 'completed' => $completedNumber, 'pending' => $pendingNumber, 'circleStatus' => $circleStatus);
			$isBannerLoaded = TRUE;
		}
		$this -> load -> view('template/headtag', array("title" => "CCPC Calling Process"));
		if ($isBannerLoaded)
			$this -> load -> view('template/statusBanner', $userStripData);
	}

	public function updateCallingStatus() {
		$this -> load -> model('chequeDetails_Model');
		$this -> load -> model('statusTable_Model');

		$micr_code = $this -> input -> post('micr_code');
		$account_number = $this -> input -> post('accountNumber');
		$tc = $this -> input -> post('tc');
		$date_chosen = $this -> m_currentDate;
		$processingChequeNumbers = $this -> input -> post('chequeNumbers');
		$isTheChequeSetPending = FALSE;

		//if update is called without proper post, we have to redirect to index. NOTE: The value of micr_code will be false only when the post is not available.
		if ($micr_code === FALSE) {
			$this -> index();
			return;
		}

		if ($this -> input -> post('callStatus') === "none") {//Then the status of the cheques is chosen by user i.e, the cheques are not pending anymore
			foreach (explode(",",$processingChequeNumbers) as $currentChequeNumber) {
				$status_value = $this -> input -> post("callStatus_$currentChequeNumber");
				$this -> statusTable_Model -> changeStatusofCheque($currentChequeNumber, $micr_code, $account_number, $tc, $date_chosen, $status_value);
			}
		} else {
			foreach (explode(",",$processingChequeNumbers) as $currentChequeNumber) {
				$this -> statusTable_Model -> changeStatusofCheque($currentChequeNumber, $micr_code, $account_number, $tc, $date_chosen, $this -> input -> post('callStatus'));
				$isTheChequeSetPending = TRUE;
			}
		}
		if ($this -> input -> post('processingChequesCategory') != "pendingCheques")
			$this -> index();
		else {
			$this -> pendingChequesList($isTheChequeSetPending);
		}
		//foreach($processingChequeNumbers as $currentChequeNumber)
	}

	public function pendingChequesList($isLastChequePending = FALSE) {
		$this -> load -> helper('form');
		$this -> load -> model('chequeDetails_Model');
		$this -> load -> model('statusTable_Model');

		//sequnce number is added to page to make the pending cheques paginated
		$chequeSet_sequenceNumber = $this -> input -> post('pendingChequeSetSequenceNumber');
		if (!isset($chequeSet_sequenceNumber))
			$chequeSet_sequenceNumber = 0;
		else if ($isLastChequePending)//If the last cheque is confirmed by caller, then there is no need to increment the sequence number for display as another pending cheque will take its place in sequnce number.
			$chequeSet_sequenceNumber++;

		$no_of_pending_cheques = $this -> statusTable_Model -> getNumberof_pending_chequeSets_byPF($this -> m_pfIndex, $this -> m_currentDate);
		if ($chequeSet_sequenceNumber >= $no_of_pending_cheques)
			$chequeSet_sequenceNumber = 0;
		$pendingChequeSet = $this -> statusTable_Model -> getPendingChequeByPF($this -> m_pfIndex, $this -> m_currentDate, $chequeSet_sequenceNumber);

		$this -> displayBanner();

		if ($pendingChequeSet) {
			foreach ($pendingChequeSet as $chequeIter) {//Adding amount and name for cheques
				$chequeIter -> AMOUNT = $this -> chequeDetails_Model -> getAmountOfCheque($chequeIter -> MICRCODE, $chequeIter -> CHEQUENO, $chequeIter -> TC, $chequeIter -> ACCOUNTNO, $chequeIter -> CHEQUEDATE);
				$chequeIter -> NAME = $this -> chequeDetails_Model -> getBenificiaryNameOfCheque($chequeIter -> MICRCODE, $chequeIter -> CHEQUENO, $chequeIter -> TC, $chequeIter -> ACCOUNTNO, $chequeIter -> CHEQUEDATE);
			}
			$branchCode = $this -> chequeDetails_Model -> getBranchCodeByAccountNumber($pendingChequeSet[0] -> ACCOUNTNO);
			$branchName = $this -> chequeDetails_Model -> getBranchNameByAccountNumber($pendingChequeSet[0] -> ACCOUNTNO);
			$pendingChequesData = array('chequeCategory' => 'pendingCheques', 'chequeSet' => $pendingChequeSet, 'branchName' => $branchName, 'branchCode' => $branchCode, 'pendingChequeSetSequenceNumber' => $chequeSet_sequenceNumber, 'totalNumberofChequeset' => $no_of_pending_cheques);
			$this -> load -> view('chequePanel', $pendingChequesData);
		} else {//Code to display that there are no more pending cheques to process
			$this -> displayNoMoreData("Pending Cheques", "No pending cheques Found!!!");
		}
	}

	public function displayNoMoreData($formTitle, $formMessage) {
		$noMoreDataInfo = array('formTitle' => $formTitle, 'formMessage' => $formMessage, 'pfIndex' => $this -> m_pfIndex, 'chosenCircle' => $this -> m_currentCircle, 'chosenDate' => $this -> m_currentDate);
		$this -> load -> view('template/no_data_available', $noMoreDataInfo);
	}

}
