<?

namespace skvCore;

class Validate {

	public function validateParams($params) {
		$resultArray = array();
		foreach($params as $k=>$v) {
			$resultArray[$k] = $this->validateParam($params, $k);
		}
		return $resultArray;
	}

	public function validateParam($params, $paramName) {
		$paramValue = NULL;
		$validateResult = $this->returnValid();
		
		foreach($params as $k=>$v) {
			if($k == $paramName) {
				$paramValue = $v;
			}
		}
		if(is_null($paramValue)) {
			$validateResult =  $this->returnError('errNotFoundParam');
		}
		
		switch($paramName) {
			case 'currency':
				$validateResult = $this->validateCurrency($paramValue);
				break;
			case 'language':
				$validateResult = $this->validateLanguage($paramValue);
				break;
			case 'help':
				$validateResult = $this->validateHelp($paramValue);
				break;
			case 'fullscreen':
				$validateResult = $this->validateFullscreen($paramValue);
				break;
			case 'denominations':
				$validateResult = $this->validateDenominations($paramValue);
				break;
		}
		
		return $validateResult;
	}
	
	private function returnValid() {
		return array(
			'valid' => true,
		);
	}
	
	private function returnError($id) {
		return array(
			'valid' => false,
			'error' => $id,
		);
	}
	
	public function validateCurrency($paramValue) {
		$regex = '/^(NAN|NaN|nan|AFN|EUR|ALL|DZD|USD|EUR|AOA|XCD|XCD|ARS|AMD|AWG|AUD|EUR|AZN|BSD|BHD|BDT|BBD|BYN|BYR|EUR|BZD|XOF|BMD|INR|BTN|BOB|BOV|USD|BAM|BWP|NOK|BRL|USD|BND|BGN|XOF|BIF|CVE|KHR|XAF|CAD|KYD|XAF|XAF|CLP|CLF|CNY|AUD|AUD|COP|COU|KMF|CDF|XAF|NZD|CRC|XOF|HRK|CUP|CUC|ANG|EUR|CZK|DKK|DJF|XCD|DOP|USD|EGP|SVC|USD|XAF|ERN|EUR|ETB|EUR|FKP|DKK|FJD|EUR|EUR|EUR|XPF|EUR|XAF|GMD|GEL|EUR|GHS|GIP|EUR|DKK|XCD|EUR|USD|GTQ|GBP|GNF|XOF|GYD|HTG|USD|AUD|EUR|HNL|HKD|HUF|ISK|INR|IDR|XDR|IRR|IQD|EUR|GBP|ILS|EUR|JMD|JPY|GBP|JOD|KZT|KES|AUD|KPW|KRW|KWD|KGS|LAK|EUR|LBP|LSL|ZAR|LRD|LYD|CHF|EUR|EUR|MOP|MKD|MGA|MWK|MYR|MVR|XOF|EUR|USD|EUR|MRO|MUR|EUR|XUA|MXN|MXV|USD|MDL|EUR|MNT|EUR|XCD|MAD|MZN|MMK|NAD|ZAR|AUD|NPR|EUR|XPF|NZD|NIO|XOF|NGN|NZD|AUD|USD|NOK|OMR|PKR|USD|PAB|USD|PGK|PYG|PEN|PHP|NZD|PLN|EUR|USD|QAR|EUR|RON|RUB|RWF|EUR|SHP|XCD|XCD|EUR|EUR|XCD|WST|EUR|STD|SAR|XOF|RSD|SCR|SLL|SGD|ANG|XSU|EUR|EUR|SBD|SOS|ZAR|SSP|EUR|LKR|SDG|SRD|NOK|SZL|SEK|CHF|CHE|CHW|SYP|TWD|TJS|TZS|THB|USD|XOF|NZD|TOP|TTD|TND|TRY|TMT|USD|AUD|UGX|UAH|AED|GBP|USD|USD|USN|UYU|UYI|UZS|VUV|VEF|VND|USD|USD|XPF|MAD|YER|ZMW|ZWL|XBA|XBB|XBC|XBD|XTS|XXX|XAU|XPD|XPT|XAG)$/';	
		if(preg_match($regex, $paramValue)) {
			return $this->returnValid();
		}
		else {
			return $this->returnError('errWrongCurrency');
		}
	}
	
	public function validateLanguage($paramValue) {
		$regex = '/^[a-zA-Z]{2}$/';
		if(preg_match($regex, $paramValue)) {
			return $this->returnValid();
		}
		else {
			return $this->returnError('errWrongLang');
		}
	}
	
	public function validateHelp($paramValue) {
		$regex = '/^[0-1]$/';
		if(preg_match($regex, $paramValue)) {
			return $this->returnValid();
		}
		else {
			return $this->returnError('errWrongBool');
		}
	}
	
	public function validateFullscreen($paramValue) {
		$regex = '/^[0-1]$/';
		if(preg_match($regex, $paramValue)) {
			return $this->returnValid();
		}
		else {
			return $this->returnError('errWrongBool');
		}
	}
	
	public function validateDenominations($paramValue) {
		$regex = '/^-?(?:\d+|\d*\.\d+)$/';
		if(!is_array($paramValue)) {
			$denominations = explode(',', $paramValue);
		}
		else {
			$denominations = $paramValue;
		}
		$min = $denominations[0];
		$c = 0;		
		
		foreach($denominations as $d) {
			$d = trim($d);
			if(!preg_match($regex, $d)) {
				return $this->returnError('errDenominationsInt');
			}
			if($d < 0) {
				return $this->returnError('errDenominationsNegative');
			}
			if($d <= $min && $c !== 0) {
				return $this->returnError('errDenominationsWrongOrder');
			}
			$min = $d;
			$c++;
		}
		
		return $this->returnValid();
		
	}
}
