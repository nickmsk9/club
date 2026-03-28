<?php

/*

CometChat
Copyright (c) 2011 Inscripts

CometChat ('the Software') is a copyrighted work of authorship. Inscripts 
retains ownership of the Software and any copies of it, regardless of the 
form in which the copies may exist. This license is not a sale of the 
original Software or any copies.

By installing and using CometChat on your server, you agree to the following
terms and conditions. Such agreement is either on your own behalf or on behalf
of any corporate entity which employs you or which you represent
('Corporate Licensee'). In this Agreement, 'you' includes both the reader
and any Corporate Licensee and 'Inscripts' means Inscripts (I) Private Limited:

CometChat license grants you the right to run one instance (a single installation)
of the Software on one web server and one web site for each license purchased.
Each license may power one instance of the Software on one domain. For each 
installed instance of the Software, a separate license is required. 
The Software is licensed only to you. You may not rent, lease, sublicense, sell,
assign, pledge, transfer or otherwise dispose of the Software in any form, on
a temporary or permanent basis, without the prior written consent of Inscripts. 

The license is effective until terminated. You may terminate it
at any time by uninstalling the Software and destroying any copies in any form. 

The Software source code may be altered (at your risk) 

All Software copyright notices within the scripts must remain unchanged (and visible). 

The Software may not be used for anything that would represent or is associated
with an Intellectual Property violation, including, but not limited to, 
engaging in any activity that infringes or misappropriates the intellectual property
rights of others, including copyrights, trademarks, service marks, trade secrets, 
software piracy, and patents held by individuals, corporations, or other entities. 

If any of the terms of this Agreement are violated, Inscripts reserves the right 
to revoke the Software license at any time. 

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

class Comet {

	protected static $api_version = "1.0.0";
	protected static $api_key = '';
	protected static $secret_key = '';

    function Comet() {
		global $beaconpush_apikey;
		global $beaconpush_secretkey;

		self::$api_key = $beaconpush_apikey;
		self::$secret_key = $beaconpush_secretkey;
    }

    function publish($args) {
		if (!$args['channel']) {
            echo('Missing Channel');
            return false;
        }

        $channel = $args['channel'];

		$sql = "insert into cometchat_apehistory (channel,message,sent) values ( '".mysql_real_escape_string($channel). "', '" . mysql_real_escape_string(serialize($args['message'])) . "','".getTimeStamp()."')";
		mysql_query($sql);

		$args['message']['message'] = str_replace("'",'%27',str_replace('"','%22',$args['message']['message']));

		Comet::_request('POST', 'channels', $channel, array('message'=>$args['message']));
   
	}

    function history($args) {
        if (!$args['channel']) {
            echo('Missing Channel');
            return false;
        }
		
		$response['messages'] = array();
		$limit   = +$args['limit'] ? +$args['limit'] : 10;
		$sql = "select message from cometchat_apehistory where channel = '".mysql_real_escape_string($args['channel'])."' order by id desc limit 0, ".$limit;
		$result = mysql_query($sql);

		while($row = mysql_fetch_array($result)) {
			$response['messages'][] = unserialize($row['message']);
		}

        return $response['messages'];
    }



	function _request($method, $command, $arg=NULL, array $data=array(), $curl_timeout=30) {

        $request_url = 'http://api.beaconpush.com/'.self::$api_version.'/'.self::$api_key;
        $request_url = $request_url.'/'.strtolower($command).($arg ? '/'.$arg : '');

        $headers = array(
            'X-Beacon-Secret-Key: '. Comet::$secret_key,
        );

        $ch = curl_init($request_url);

        $opts = array(
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => ($curl_timeout/2 < 3 ? 3 : floor($curl_timeout/2)),
            CURLOPT_TIMEOUT => $curl_timeout,
        );

        curl_setopt_array($ch, $opts);

        if($method == 'POST')
        {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        elseif($method == 'GET')
            curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
        elseif($method == 'DELETE')
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        else
            throw new Exception('Illegal method defined. Allowed methods are POST, GET and DELETE.');


        if(($response = curl_exec($ch)) === FALSE)
            throw new Exception('cURL failed: '.curl_error($ch));

        curl_close($ch);
    }

}

?>