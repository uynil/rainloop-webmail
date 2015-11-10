<?php

class PdoMessagePlugin extends \RainLoop\Plugins\AbstractPlugin
{
	public function Init()
	{
		$this->addHook('pdo.save-message', 'PdoSaveMessage');
	}

	/**
	 * @param string $oMessageList
	 * @param string $sLogin
	 * @param string $sPassword
	 *
	 * @throws \RainLoop\Exceptions\ClientException
	 */
	public function PdoSaveMessage(&$oMessageList)
	{
		\Rainloop\ChromePhp::log('LYY in plugins');
		\Rainloop\ChromePhp::log($oMessageList->NewMessages);
		\Rainloop\ChromePhp::log($oMessageList->sFolder);
		\Rainloop\ChromePhp::log($oMessageList->sSubject);
		$resultSaveMessage = false;
		if ($resultSaveMessage)
		{
			throw new \RainLoop\Exceptions\ClientException(\RainLoop\Notifications::AccountNotAllowed);
		}
	}

	/**
	 * @return array
	 */
	public function configMapping()
	{
		return array(
			\RainLoop\Plugins\Property::NewInstance('mysql')->SetLabel('mysql')
				->SetType(\RainLoop\Enumerations\PluginPropertyType::BOOL)
				->SetDescription('Use Mysql As Pdo Backend.')
				->SetDefaultValue(true),
			\RainLoop\Plugins\Property::NewInstance('pdo_settings')->SetLabel('Use System Mysql List')
				->SetType(\RainLoop\Enumerations\PluginPropertyType::BOOL)
				->SetDescription('Use System Settings.')
				->SetDefaultValue(true)
		);
	}
}
