<?php
/**
 * This file is part of plesk-cloudns.
 *
 * To work, this file needs to be placed at /usr/local/psa/admin/plib/registry/EventListener/ 
 * and credentials filled in.
 *
 * @package plesk-cloudns-event
 * @license http://www.gnu.org/licenses/lgpl.txt GNU LESSER GENERAL PUBLIC LICENSE v3
 *
 * @author Nick Andriopoulos <nand@lambda-twelve.com>
 */
 
/**
 * Our class
 */
class ClouDnsSlaveManager implements EventListener
{
  // Get your API user settings in https://www.cloudns.net/api-settings/
  private $authid   = ''; // Add your API auth-id
  private $authkey  = ''; // Add your API auth-key
  private $masterip = ''; // (Optional) Add your server primary ip here (ClouDNS will talk with this IP)
  private $baseurl  = 'https://api.cloudns.net/'; 

  /**
   * @see https://docs.plesk.com/en-US/onyx/extensions-guide/plesk-features-available-for-extensions/subscribe-to-plesk-events.71093/
   */
  public function handleEvent($objectType, $objectId, $action, $oldValues,$newValues) {
    if($this->authid == '') { 
      error_log('ClouDNS credentials empty, doing nothing.');
    }
  
    switch($objectType) {
      case 'domain_alias':
        switch($action) {
          case 'domain_alias_create':
            $this->addSlave($newValues['Domain Alias Name']);
            break;
          case 'domain_alias_delete':
            $this->delSlave($oldValues['Domain Alias Name']);
            break;
          default:
            // Do nothing, updates are handled by zone transfers.
        }
        break;
      case 'domain':
        switch($action) {
          case 'domain_create':
            $this->addSlave($newValues['Domain Name']);
            break;
          case 'domain_delete':
            $this->delSlave($oldValues['Domain Name']);
            break;
          default:
            // Do nothing, updates are handled by zone transfers.
        }
        break;
      default:
        // Do we need more objectTypes? Open an issue here:
        // https://github.com/lambdatwelve/plesk-cloudns/issues
        return;
    }
  }

  /**
   * Invokes the API to add a slave zone.
   *
   * @param string $zone  The zone to create.
   * 
   * @return void
   */
  private function addSlave($zone) {
    $params = array(
     'domain-name' => $zone,
     'zone-type'   => 'slave',
     'master-ip'   => $this->masterip,
    );  

    $response = $this->apiCall('dns/register.json', $params);

    if($response['status'] == 'Failed') {
     error_log('Failed to create '.$zone.' with message '.$response['statusDescription']);
    }
  }

  /**
   * Invokes the API to delete a slave zone.
   *
   * @param string $zone  The zone to create.
   * 
   * @return void
   */
  private function delSlave($zone) {
    $params = array(
      'domain-name' => $zone
    );
    $response = $this->apiCall('dns/delete.json', $params);

    if($response['status'] == 'Failed') {
      error_log('Failed to delete '.$zone.' with message '.$response['statusDescription']);
    }
  
  }

  /**
   * Actual API caller
   *
   * @param string $url   The API endpoint to call upon.
   * @param array  $data  Associative array that gets sent as query
   * 
   * @return array
   */
  function apiCall ($url, $data) {
    $url  = $this->baseurl.$url;

    // Add auth params to the call.
    $auth = array(
      'auth-id'       => $this->authid,
      'auth-password' => $this->authkey
    );
    $params = $auth + $data;

    $init = curl_init();
    curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($init, CURLOPT_URL,            $url);
    curl_setopt($init, CURLOPT_POST,           true);
    curl_setopt($init, CURLOPT_POSTFIELDS,     http_build_query($params));

    $content = curl_exec($init);
    curl_close($init);
    return json_decode($content, true);
  }

}
// Plesk expects us to return an instance of our class.
return new ClouDnsSlaveManager();
