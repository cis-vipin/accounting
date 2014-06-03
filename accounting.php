<?php


class accounting
{
  
  public $default_url = 'http://localhost/frontaccounting/modules/api/index.php/';

  function __construct() {
      $this->CI = & get_instance();
   }

   ## This function is use to fetch ledger code from 
   ## Table FA_ledger_codes using account name
   function fetch_ledger_code($name) {
      $this->CI->db->select('account_code,account_code2');
      $this->CI->db->from('FA_ledger_codes');
      $this->CI->db->where('account_name', $name);
      $query = $this->CI->db->get(); 
      $result = $query->row();
      return $result;
   }
  
  public function do_post_ledger($method,$last_id) {

    if($last_id == 0) {
      $name="Sales";
      $sales_acc = $this->fetch_ledger_code($name);
    } else {
      $name = "Purchase";
      $sales_acc = $this->fetch_ledger_code($name);
    }

    $url = $this->default_url."ledger_trans/";
    $getData = array('test'=> 'test');
    $tran_no = $this->jsonDecode($this->curlUsingGet($url, $getData)); 

    ### Post ledger entry into gl ledger
    $data = array();
    $data['type_no'] =  $tran_no->type_no;
    $data['tran_date'] = date("Y-m-d");
    $data['account'] = $sales_acc->account_code;
    $data['memo'] = "Test entry for memo";
    $data['amount'] = 50;
    $data['action'] = 'do_post';
    $data['type'] = $method;
    $data['last_id'] = $last_id;

    ###
    ####### Call FA API To post data for gl leger entry
    ###
    $result =  $this->jsonDecode($this->curlUsingPost($url,$data));
    return $tran_no;
  }

  

  ## Curl Function to post or get data from FA API
  ## URL - to fetch or post data 
  ## $data - variable array  ($data = array('first_name' => 'John', 'email' => 'john@example.com', 'phone' => '1234567890');)
  ##  $method - Post or Get method 
  function curlUsingPost($url, $data)
  {

    if(empty($url) OR empty($data))
    {
        return 'Error: invalid Url or Data';
    }

   
    //url-ify the data for the POST
    $fields_string = '';
    foreach($data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
    $fields_string = rtrim($fields_string,'&');


    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_POST,count($data));
    curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);

    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10); # timeout after 10 seconds, you can increase it
    //curl_setopt($ch,CURLOPT_HEADER,false);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);  # Set curl to return the data instead of printing it to the browser.
    curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)"); # Some server may refuse your request if you dont pass user agent
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    //execute post
    $result = curl_exec($ch);

    //close connection
    curl_close($ch);
    return $result;
  }

  ##$data = array('first_name' => 'John', 'email' => 'john@example.com', 'phone' => '1234567890',  );
  ##echo curlUsingGet('http://www.silverphp.com/', $data);
  function curlUsingGet($url, $data)
  {

    if(empty($url) OR empty($data))
    {
    return 'Error: invalid Url or Data';
    }

    //url-ify the data for the get  : Actually create datastring
    $fields_string = '';

    foreach($data as $key=>$value){
    $fields_string[]=$key.'='.urlencode($value).'&amp;'; }
    $urlStringData = $url.'?'.implode('&amp;',$fields_string);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10); # timeout after 10 seconds, you can increase it
    curl_setopt($ch, CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
    curl_setopt($ch, CURLOPT_URL, $urlStringData ); #set the url and get string together

    $return = curl_exec($ch);
    curl_close($ch);
    return $return;
  }

  ## This function is for decode json data return by API 
  function jsonDecode($data) { 
    return json_decode($data);
  }

  ## This function is for encode json data
  function jsonEncode($data) {
    return json_encode($data);
  }


}
