<?php
class StatusTable_Model extends CI_Model {

	public function __construct() {
		$this -> load -> database();
	}

	function getLockedChequeByPF($pfIndex, $chequedate) {
		$this -> db -> where('lock_pf', $pfIndex);
		$this -> db -> where('chequedate', $chequedate);
		$this -> db -> where('status_flag', 'NOW');
		$query = $this -> db -> get('status_table');
		return $query -> result();
	}

	function changeStatusofCheque($cheque_number, $micr_code, $account_number, $tc, $cheque_date, $staus_value) {
		if ($staus_value === '') {
			return FALSE;
		}
		$this -> db -> where('CHEQUENO', $cheque_number) -> where('MICRCODE', $micr_code) -> where('TC', $tc) -> where('ACCOUNTNO', $account_number) -> where('CHEQUEDATE', $cheque_date) -> set("STATUS_FLAG", $staus_value);
		return $this -> db -> update('status_table');
		//return TRUE;
	}

	function getPendingChequeByPF($pfIndex, $chequedate, $sequence) {
		//Finding the HVT Instrument that is pending
		$sqlQuery = "select accountno, sum(amount) from ccpc_hvt_instruments_calling 
				where accountno in(
				SELECT accountno FROM `status_flag_values` sf, status_table st WHERE
				st.chequedate = '$chequedate' and st.lock_pf = '$pfIndex' 
				and sf.status_type = 'pending' and 
				sf.status_flag = st.status_flag 
				group by accountno)
				and chequedate = '$chequedate' 
				group by accountno order by sum(amount) desc LIMIT $sequence,1";

		$query = $this -> db -> query($sqlQuery);

		if ($query -> num_rows() > 0) {
			$topAccountno = $query -> row() -> accountno;
			$sqlQuery = "SELECT st.* FROM `status_flag_values` sf, status_table st WHERE 
							st.chequedate = '$chequedate' and 
							st.lock_pf = '$pfIndex' and 
							sf.status_type = 'pending' and 
							sf.status_flag = st.status_flag and  
							st.accountno = '$topAccountno'";
			$queryST = $this -> db -> query($sqlQuery);
			return $queryST -> result();
		} else {
			return FALSE;
		}
	}

	function getNumberof_pending_chequeSets_byPF($pfIndex, $chequedate) {
		$sqlQuery = "SELECT count(distinct(accountno)) as 'NO_PENDING' FROM `status_flag_values` sf, status_table st WHERE
				st.chequedate = '$chequedate' and st.lock_pf = '$pfIndex' 
				and sf.status_type = 'pending' and 
				sf.status_flag = st.status_flag";
		return $this->db->query($sqlQuery)->row()->NO_PENDING;
	}

}
