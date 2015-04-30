<?php
class Banner_Model extends CI_Model {

        public function __construct()
        {
             $this->load->database();
        }
		
		public function get_completed_status($pfIndex, $chosen_date)
		{
			$sqlQuery = "SELECT count(*) as 'COMPLETED_NO' from status_table st, status_flag_values sfv where sfv.status_type = 'Confirmed' and st.chequedate = '".$chosen_date."' and sfv.status_flag = st.status_flag and st.lock_pf = '".$pfIndex."'";
			$query = $this->db->query($sqlQuery);
			if ($query->num_rows() == 1){
				return $query->row()->COMPLETED_NO;
			}
			//When it reaches here, it means either pf index or date is missing.
			return "0";
		}
		
		public function get_pending_status($pfIndex, $chosen_date)
		{
			$sqlQuery = "SELECT count(*) as 'PENDING_NO' from status_table st, status_flag_values sfv where sfv.status_type = 'Pending' and st.chequedate = '".$chosen_date."' and sfv.status_flag = st.status_flag and st.lock_pf = '".$pfIndex."'";
			$query = $this->db->query($sqlQuery);
			if ($query->num_rows() == 1){
				return $query->row()->PENDING_NO;
			}
			return "0";
		}
		
		function getCircle_TotalChequeNumber($circleName, $chosen_date)
		{
			$sqlQuery_Total = "SELECT count(*) as 'NO_OF_CHEQUE' from ccpc_hvt_instruments_calling ic_full, micr_circles mc where  mc.circle_name = '".$circleName."' and ic_full.chequedate = '".$chosen_date."' and ic_full.MICRCODE  between  mc.micr_from and mc.micr_to";
			$query=$this->db->query($sqlQuery_Total);
			return $query->row()->NO_OF_CHEQUE;
		}
		
		function getCircle_ConfirmedChequeNumber($circleName, $chosen_date)
		{
			$sqlQuery_Confirmed = "SELECT count(*) as 'CNF_CHEQUE' from micr_circles mc, status_table st, status_flag_values sfv where  st.status_flag = sfv.status_flag and sfv.status_type = 'Confirmed' and mc.circle_name = '".$circleName."' and st.chequedate = '".$chosen_date."' and st.MICRCODE between mc.micr_from and mc.micr_to";
			$query=$this->db->query($sqlQuery_Confirmed);
			return $query->row()->CNF_CHEQUE;
		}
		
		function getCircleNames()
		{
			$this->db->select('circle_name');
			$this->db->order_by('circle_name', 'ASC');
			$query = $this->db->get('micr_circles');
			$circleNames;
			foreach ($query->result() as $row)
			{
				$circleNames[] = $row->circle_name;
			}
			return $circleNames;
		}
}