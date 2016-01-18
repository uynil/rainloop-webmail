<?php
class CasLoginPlugin extends \RainLoop\Plugins\AbstractPlugin
{   
    /**
     * @var \RainLoop\Providers\AccountManagement
     */
    private $oAccountManagementProvider;

    public function Init()
    {
        // $this->addHook('filter.login-credentials', 'FilterLoginCredentials');
        // $this->addHook('main.farbrica', 'MainFabrica');
        // $this->addHook('filter.app-data', FilterAppData);
        // $this->addHook('filter.http-paths', FilterHttpPaths)
        $this->addHook('filter.application-config', FilterApplicationConfig);
        $this->addHook('filter.pre-do-login', FilterPreDoLogin);
        $this->addHook('service.after-logout', ServiceAfterLogout); 
        $this->addJs('js/include.js');
    }

    // public function Support()
    // {
    //     if(!\class_exists(phpCas))
    //     {
    //         return 'phpCas extension should be installed before enabling this plugin';
    //     }
    // }

    public function AccountManagementProvider($oConfig)
    {
        $sDsn = \trim($oConfig->get('contacts', 'pdo_dsn', ''));
        $sUser = \trim($oConfig->get('contacts', 'pdo_user', ''));
        $sPassword = (string) $oConfig->get('contacts', 'pdo_password', '');

        $sDsnType = \trim($oConfig->get('contacts', 'type', 'sqlite'));
        
        RainLoop\ChromePhp::log("in AMP:".$sDsn.";".$sUser.";".$sPassword.";".$sDsnType);
        $oDriver = new \RainLoop\Providers\AccountManagement\PdoAccountManagement($sDsn, $sUser, $sPassword, $sDsnType);
        RainLoop\ChromePhp::log("after oDriver");

        $oAccountManagementProvider = new \RainLoop\Providers\AccountManagement($oDriver);
        return $oAccountManagementProvider;
    }


    public function FilterApplicationConfig(&$oConfig)
    {

        phpCAS::setDebug('/tmp/phpCAS-rl.log'); // Schrijft debug informatie naar een log-file

        // Parameters: CAS version, CAS server url, CAS server port, CAS server URI (same as host), 
        // boolean indicating session start, communication protocol (SAML) between application and CAS server
        phpCAS::client(CAS_VERSION_3_0,'192.168.31.173',8443,'', true, 'saml');

        // Server from which logout requests are sent
        // phpCAS::handleLogoutRequests(true, array('cas1.ugent.be','cas2.ugent.be','cas3.ugent.be','cas4.ugent.be','cas5.ugent.be','cas6.ugent.be'));
        phpCAS::handleLogoutRequests(true,array('http://localhost:8080/php_cas_login/home.html'));

        // Path to the "trusted certificate authorities" file:
        // phpCAS::setCasServerCACert('/etc/ssl/certs/ca-certificates.crt');
        // No server verification (less safe!):
        phpCAS::setNoCasServerValidation();
        // The actual user authentication
        phpCAS::forceAuthentication(); 

        RainLoop\ChromePhp::log("in cas login redirect page");

        $this->oAccountManagementProvider = $this->AccountManagementProvider($oConfig);
    }

    public function FilterPreDoLogin($sLogin, &$sEmail, &$sPassword)
    {
        $sUser = phpCAS::getUser();
        if ($sUser !== '' && $sUser == 'admin')
        {
            // TODO Admin login 
            $sEmail = $sUser;
            $sPassword = '12345';
        }
        else
        {
            $aResult = array();
            $aResult = $this->oAccountManagementProvider->GetEmailAndPassword($sUser);
            $sLogin = $sUser;
            $sEmail = $aResult['email'];
            $sPassword = $aResult['passwd'];
            \RainLoop\ChromePhp::log('111111111111111111111111"'.$aResult['email']); 
        }

    }

    public function ServiceAfterLogout()
    {
        // Handle logout requests
        phpCAS::logout();
    }

    public function configMapping()
    {
        return Array(
            \RainLoop\Plugins\Property::NewInstance('cas_server_url')->SetLabel('cas_server_ul')
                ->SetType(\RainLoop\Enumerations\PluginPropertyType::STRING_TEXT)
                ->SetDescription('The url of cas server service.')
                ->SetDefaultValue('https://localhost'),
            \RainLoop\Plugins\Property::NewInstance('cas_server_port')->SetLabel('cas_server_port')
                ->SetType(\RainLoop\Enumerations\PluginPropertyType::STRING_TEXT)
                ->SetDescription('The port of server url.')
                ->SetDefaultValue('8443'),
            \RainLoop\Plugins\Property::NewInstance('label_3')->SetLabel('lable_3')
                ->SetType(\RainLoop\Enumerations\PluginPropertyType::BOOL)
                ->SetDescription('Throw an label 3 error instead of an access error.')
                ->SetDefaultValue(true)
                ); 
    }
}
