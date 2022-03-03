<?php

namespace Rkesa\Email\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Log;
use Exception;
use Illuminate\Http\Request;

include_once base_path('packages/Rkesa/Email/vendor/afterlogic_webmail/libraries/afterlogic/api.php');

class DomainController extends Controller
{
    public function index()
    {
        $my_domains = [];
        if (class_exists('CApi') && \CApi::IsValid()) {
            $oApiDomainsManager = \CApi::Manager('domains');

            $domains = array_values($oApiDomainsManager->getFullDomainsList());
            foreach($domains as $key => $domain){
                $cdomain = $oApiDomainsManager->getDomainByName($domain[1]);
                $my_domain['domain_id'] = $cdomain->IdDomain;
                $my_domain['domain_name'] = $domain[1];
                $my_domain['incoming_mail_server'] = $cdomain->IncomingMailServer;
                $my_domain['incoming_mail_port'] = $cdomain->IncomingMailPort;
                $my_domain['incoming_mail_use_ssl'] = $cdomain->IncomingMailUseSSL;
                $my_domain['outgoing_mail_server'] = $cdomain->OutgoingMailServer;
                $my_domain['outgoing_mail_port'] = $cdomain->OutgoingMailPort;
                $my_domain['outgoing_mail_use_ssl'] = $cdomain->OutgoingMailUseSSL;
                $my_domain['remove_flag'] = false;
                $my_domains []= $my_domain;
            }
        } else {
            throw new Exception('WebMail API isn\'t available');
        }
        return $my_domains;
    }

    public function update(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $domains = $request->input('domains', []);
            if (class_exists('CApi') && \CApi::IsValid()) {
                $oApiDomainsManager = \CApi::Manager('domains');

                foreach($domains as $domain){
                    if ($domain['remove_flag']){
                        if (!$oApiDomainsManager->deleteDomains([$domain['domain_id']])) {
                            $res->errcode = 1;
                            $res->errmess = $oApiDomainsManager->GetLastErrorMessage();
                        }
                    }else {
                        if ($domain['domain_id'] != 0) {
                            $oDomain = $oApiDomainsManager->getDomainById($domain['domain_id']);
                            $oDomain->IncomingMailServer = $domain['incoming_mail_server'];
                            $oDomain->IncomingMailPort = $domain['incoming_mail_port'];
                            $oDomain->IncomingMailUseSSL = $domain['incoming_mail_use_ssl'];
                            $oDomain->OutgoingMailServer = $domain['outgoing_mail_server'];
                            $oDomain->OutgoingMailPort = $domain['outgoing_mail_port'];
                            $oDomain->OutgoingMailUseSSL = $domain['outgoing_mail_use_ssl'];
                            if (!$oApiDomainsManager->updateDomain($oDomain)) {
                                $res->errcode = 1;
                                $res->errmess = $oApiDomainsManager->GetLastErrorMessage();
                            }
                        } else {
                            $oDomain = new \CDomain($domain['domain_name']);
                            $oDomain->IncomingMailServer = $domain['incoming_mail_server'];
                            $oDomain->IncomingMailPort = $domain['incoming_mail_port'];
                            $oDomain->IncomingMailUseSSL = $domain['incoming_mail_use_ssl'];
                            $oDomain->OutgoingMailServer = $domain['outgoing_mail_server'];
                            $oDomain->OutgoingMailPort = $domain['outgoing_mail_port'];
                            $oDomain->OutgoingMailUseSSL = $domain['outgoing_mail_use_ssl'];
                            if (!$oApiDomainsManager->createDomain($oDomain)) {
                                $res->errcode = 1;
                                $res->errmess = $oApiDomainsManager->GetLastErrorMessage();
                            }
                        }
                    }
                }
            } else {
                $res->errcode = 1;
                $res->errmess = 'WebMail API isn\'t available';
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
