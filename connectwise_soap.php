<?php

/*
 * put this file on application/libraries/
 */
class Connectwise_Soap
{
	private $_wsdlType = '';
	private $_wsdl = '';
	private $_soapClient = false;
	private $_ci;

	private $_integratorLoginId = 'XXXXXXX';		/// integrator Login Id
	private $_integratorPassword = 'XXXXXXXX';		/// integrator password
	private $_companyId = 'XXXXXXXX';				/// company id

	/// You can add all the wsdls in this array... just make sure you call them based on the key of this array  ///
	private $_wsdlArray = array('time_entry' => 'https://your_company.com/v4_6_release/apis/2.0/TimeEntryApi.asmx?wsdl',
								'scheduling' => 'https://your_company.com/v4_6_release/apis/2.0/SchedulingApi.asmx?wsdl',
								'company' => 'https://your_company.com/v4_6_release/apis/2.0/CompanyApi.asmx?wsdl',
								'service_ticket' => 'https://your_comapny.com/v4_6_release/apis/2.0/ServiceTicketApi.asmx?wsdl',
								'contact' => 'https://your_company.com/v4_6_release/apis/2.0/ContactApi.asmx?wsdl',
							);

	public function __construct($option = array())
	{
		$this->_ci = &get_instance();

		if(isset($option['wsdl']) && trim($option['wsdl']) !== '')
		{
			$this->setWSDL($option['wsdl']);
			$this->_soapClient = new SoapClient($this->_wsdl);
		}
	}

	public function getWSDL()
	{
		return $this->_wsdl;
	}

	public function setWSDL($wsdlType)
	{
		$wsdlType = trim($wsdlType);
		if(!isset($this->_wsdlArray[$wsdlType]))
			throw new Exception("Available WSDL types are [".implode(", ", array_keys($this->_wsdlArray))."]");

		$this->_wsdlType = $wsdlType;
		if(($wsdl = trim($this->_wsdlArray[$wsdlType])) !== '')
		{
			$createSoapClient = false;

			if(trim($this->_wsdl) === '' || trim($this->_wsdl) !== trim($wsdl))
				$createSoapClient = true;

			$this->_wsdl = $wsdl;

			if($createSoapClient === true)
				$this->_soapClient = new SoapClient($this->_wsdl);
		}
		else
			throw new Exception('no WSDL has been defined for type ['.$wsdlType.']');

		return $this;
	}

	public  function getSoapClient()
	{
		return $this->_soapClient;
	}

	private function _populdateCredentialsForParam($functionParam)
	{
		if(is_array($functionParam) || is_object($functionParam))
		{
			if(is_array($functionParam))
			{
				if(!isset($functionParam['credentials']))
					$functionParam['credentials'] = array('CompanyId' => $this->_companyId, 'IntegratorLoginId' => $this->_integratorLoginId, 'IntegratorPassword' => $this->_integratorPassword);
			}
			else
			{
				if(!isset($functionParam->credentials))
					$functionParam->credentials = (object)array('CompanyId' => $this->_companyId, 'IntegratorLoginId' => $this->_integratorLoginId, 'IntegratorPassword' => $this->_integratorPassword);
			}
		}
		else
			throw new Exception("Parameter provided is NOT VALID!!!");

		return $functionParam;
	}

	public function execute($functionName, $functionParam)
	{
		try
		{
			if(trim($this->_wsdl) === '')
				throw new Exception("No WSDL specified");

			if(!is_object($this->_soapClient))
				$this->_soapClient = new SoapClient($this->_wsdl);

			if(!is_object($this->_soapClient))
				throw new Exception("Invalid WSDL [".$this->_wsdl."]. Cannot generate SoapClient object!!!!");

			$functionParam = $this->_populdateCredentialsForParam($functionParam);

			$output = $this->_soapClient->$functionName($functionParam);
			return $output;
		}
		catch(Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * This helper function converts the datetime string received from ConnectWise (dateTtime) to Normal Date Time
	 * @param String $dateTime
	 * @param Boolean $date
	 * @param Boolean $time
	 *
	 * @return String (Date / Time / DateTime)
	 */
	private function _parseCWDateTime($dateTime, $date = true, $time = true)
	{
		$output = array();

		list($dt, $tm) = explode("T", $dateTime);
		if($date === true)
			$output[] = $dt;

		if($time === true)
			$output[] = $tm;

		if(count($output) > 0)
			$output = implode(" ", $output);
		else
			$output = $dateTime;

		return $output;
	}

}