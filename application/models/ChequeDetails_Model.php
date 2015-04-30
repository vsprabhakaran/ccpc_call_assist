<?php
class ChequeDetails_Model extends CI_Model {

	public function __construct() {
		$this -> load -> database();
	}

	function getAmountOfCheque($micr_code, $cheque_number, $tc, $account_no, $cheque_date) {
		$this -> db -> where('micrcode', $micr_code) -> where('chequeno', $cheque_number) -> where('tc', $tc) -> where('accountno', $account_no) -> where('chequedate', $cheque_date);
		$query = $this -> db -> get('ccpc_hvt_instruments_calling');
		if ($query -> num_rows() == 1) {
			return $query -> row() -> AMOUNT;
		} else {
			return "";
		}
	}

	function getBenificiaryNameOfCheque($micr_code, $cheque_number, $tc, $account_no, $cheque_date) {
		$this -> db -> where('micrcode', $micr_code) -> where('chequeno', $cheque_number) -> where('tc', $tc) -> where('accountno', $account_no) -> where('chequedate', $cheque_date);
		$query = $this -> db -> get('ccpc_hvt_instruments_calling');
		if ($query -> num_rows() == 1) {
			return $query -> row() -> NAME;
		} else {
			return "";
		}
	}

	function getBranchCodeByAccountNumber($accountno) {
		$this -> db -> where('accountno', $accountno) -> limit(1);
		$query = $this -> db -> get('ccpc_hvt_instruments_calling');
		if ($query -> num_rows() == 1) {
			return $query -> row() -> BRANCHCODE;
		} else {
			return "";
		}

	}

	function getBranchNameByAccountNumber($accountno) {
		$this -> db -> where('accountno', $accountno) -> limit(1);
		$query = $this -> db -> get('ccpc_hvt_instruments_calling');
		if ($query -> num_rows() == 1) {
			return $query -> row() -> BRANCHNAME;
		} else {
			return "";
		}

	}

	function findNext_HVTChequeSet_AccountNumber($chosen_circle, $chosen_date) {
		$sqlQuery = "SELECT accountno from ccpc_hvt_instruments_calling ic_full, micr_circles mc where
			  (accountno NOT IN (select accountno from status_table)) and 
			  mc.circle_name = '$chosen_circle' and 
			  ic_full.MICRCODE  between  mc.micr_from and mc.micr_to and 
			  chequedate = '$chosen_date'  
			  group by accountno 
			  order by sum(amount) 
			  DESC LIMIT 0,1";
		$query = $this -> db -> query($sqlQuery);
		if ($row = $query -> result()) {
			return $query -> row() -> accountno;
		} else {
			return FALSE;
		}
	}

	function getChequeSet_For_AccountNumber($account_number, $chosen_date) {
		$this -> db -> where('ACCOUNTNO', $account_number) -> where('CHEQUEDATE', $chosen_date);
		$query = $this -> db -> get('ccpc_hvt_instruments_calling');
		return $query -> result();
	}

	function insertInto_StatusTable_From_ICTable($tableData, $lockPfIndex) {
		foreach ($tableData as $row) {
			$insertData[] = array('MICRCODE' => $row -> MICRCODE, 'CHEQUENO' => $row -> CHEQUENO, 'TC' => $row -> TC, 'ACCOUNTNO' => $row -> ACCOUNTNO, 'CHEQUEDATE' => $row -> CHEQUEDATE, 'STATUS_FLAG' => "NOW", 'LOCK_PF' => $lockPfIndex);
		}
		if ($this -> db -> insert_batch('status_table', $insertData))
			return TRUE;
		else {
			return FALSE;
		}
	}

}
