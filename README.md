# connectwise_soap_api

This is a library class for a CI Project to connect to the ConnectWise API using SOAP protocol. 
A sample code of how to call the library is as follows:

$this->load->library('connectwise_soap');

try
{
  $wsdlType = "time_entry";
  $this->connectwise_soap->setWSDL($wsdlType);

  $output = $this->connectwise_soap->execute('FindTimeEntries', (object)array('conditions' => 'bla bla bla'));
}
catch(Exception $ex)
{
  throw $ex;
}
