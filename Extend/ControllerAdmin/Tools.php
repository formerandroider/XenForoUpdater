<?php

class LiamW_XenForoUpdater_Extend_ControllerAdmin_Tools extends XFCP_LiamW_XenForoUpdater_Extend_ControllerAdmin_Tools
{
	public function actionUpdateXenForo()
	{
		return $this->responseView('', 'liam_xenforo_update_initial');
	}

	public function actionUpdateXenForoStepCredentials()
	{
		$this->_assertPostOnly();

		if (!$this->isConfirmedPost())
		{
			return $this->responseNoPermission();
		}

		return $this->responseView('', 'liam_xenforo_update_credentials');
	}

	public function actionUpdateXenForoStepLicense()
	{
		$this->_assertPostOnly();

		if (!$this->isConfirmedPost())
		{
			return $this->responseNoPermission();
		}

		$data = $this->_input->filter(array(
			'email' => XenForo_Input::STRING,
			'password' => XenForo_Input::STRING,
		));

		if (!$data['email'] || !$data['password'])
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_invalid_credentials'));
		}

		$licenses = $this->_getLicenses($data['email'], $data['password'], $cookies);

		if (!count($licenses))
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_invalid_credentials_or_no_licenses'));
		}

		$viewParams = array(
			'licenses' => $licenses,
			'cookies' => $cookies
		);

		return $this->responseView('', 'liam_xenforo_update_licenses', $viewParams);
	}

	public function actionUpdateXenForoStepVersion()
	{
		$this->_assertPostOnly();

		if (!$this->isConfirmedPost())
		{
			return $this->responseNoPermission();
		}

		$data = $this->_input->filter(array(
			'license_id' => XenForo_Input::STRING,
			'cookies' => XenForo_Input::STRING,
		));

		if (!$data['cookies'])
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_cookies_missing'));
		}

		if (!$data['license_id'])
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_must_select_license'));
		}

		$downloadVersions = $this->_getVersions($data['cookies'], $data['license_id']);

		$viewParams = array(
			'versions' => $downloadVersions,
			'licenseId' => $data['license_id'],
			'cookies' => $data['cookies']
		);

		return $this->responseView('', 'liam_xenforo_update_version', $viewParams);
	}

	public function actionUpdateXenForoStepUpdate()
	{
		$this->_assertPostOnly();

		if (!$this->isConfirmedPost())
		{
			return $this->responseNoPermission();
		}

		@set_time_limit(0);
		@ignore_user_abort(true);

		$data = $this->_input->filter(array(
			'download_version_id' => XenForo_Input::STRING,
			'license_id' => XenForo_Input::STRING,
			'cookies' => XenForo_Input::STRING,
		));

		if (!$data['license_id'])
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_must_select_license'));
		}

		if (!$data['cookies'])
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_cookies_missing'));
		}

		$this->_downloadAndCopyZip($data['cookies'], $data['download_version_id'], $data['license_id']);

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, '/install/index.php?upgrade/');
	}

	public function actionUpdateXenForoRM()
	{
		return $this->responseView('', 'liam_xenforo_update_rm_initial');
	}

	public function actionUpdateXenForoRMStepCredentials()
	{
		$this->_assertPostOnly();

		if (!$this->isConfirmedPost())
		{
			return $this->responseNoPermission();
		}

		return $this->responseView('', 'liam_xenforo_update_rm_credentials');
	}

	public function actionUpdateXenForoRMStepLicense()
	{
		$this->_assertPostOnly();

		if (!$this->isConfirmedPost())
		{
			return $this->responseNoPermission();
		}

		$data = $this->_input->filter(array(
			'email' => XenForo_Input::STRING,
			'password' => XenForo_Input::STRING,
		));

		if (!$data['email'] || !$data['password'])
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_invalid_credentials'));
		}

		$licenses = $this->_getLicenses($data['email'], $data['password'], $cookies, 'xfresource');

		if (!count($licenses))
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_invalid_credentials_or_no_licenses'));
		}

		$viewParams = array(
			'licenses' => $licenses,
			'cookies' => $cookies
		);

		return $this->responseView('', 'liam_xenforo_update_rm_licenses', $viewParams);
	}

	public function actionUpdateXenForoRMStepVersion()
	{
		$this->_assertPostOnly();

		if (!$this->isConfirmedPost())
		{
			return $this->responseNoPermission();
		}

		$data = $this->_input->filter(array(
			'license_id' => XenForo_Input::STRING,
			'cookies' => XenForo_Input::STRING,
		));

		if (!$data['cookies'])
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_cookies_missing'));
		}

		if (!$data['license_id'])
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_must_select_license'));
		}

		$downloadVersions = $this->_getVersions($data['cookies'], $data['license_id'], 'xfresource');

		$viewParams = array(
			'versions' => $downloadVersions,
			'licenseId' => $data['license_id'],
			'cookies' => $data['cookies']
		);

		return $this->responseView('', 'liam_xenforo_update_rm_version', $viewParams);
	}

	public function actionUpdateXenForoRMStepUpdate()
	{
		$this->_assertPostOnly();

		if (!$this->isConfirmedPost())
		{
			return $this->responseNoPermission();
		}

		@set_time_limit(0);
		@ignore_user_abort(true);

		$data = $this->_input->filter(array(
			'download_version_id' => XenForo_Input::STRING,
			'license_id' => XenForo_Input::STRING,
			'cookies' => XenForo_Input::STRING,
		));

		if (!$data['license_id'])
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_must_select_license'));
		}

		if (!$data['cookies'])
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_cookies_missing'));
		}

		$this->_downloadAndCopyZip($data['cookies'], $data['download_version_id'], $data['license_id'], 'xfresource');

		$this->_request->setParam('addon_id', 'XenResource');
		$this->_request->setParam('server_file',
			realpath(XenForo_Application::getInstance()->getRootDir() . '/library/XenResource/addon-XenResource.xml'));

		return $this->responseReroute('XenForo_ControllerAdmin_AddOn',
			'Upgrade'); // Use the default add-on upgrade system for the rest.
	}

	protected function _getLicenses($email, $password, &$cookies, $type = 'xenforo')
	{
		$client = XenForo_Helper_Http::getClient("https://xenforo.com/customers/login", array(
			'useragent' => 'XenForo CLI Updater (by Liam W)'
		));
		$client->setCookieJar();
		$client->setParameterPost('email', $email);
		$client->setParameterPost('password', $password);
		$client->setParameterPost('redirect', 'customers');

		$loginResponse = $client->request('POST');

		$domQuery = new Zend_Dom_Query($loginResponse->getBody());
		$licensesQuery = $domQuery->query('.licenses .license');

		$licenses = array();

		foreach ($licensesQuery as $license)
		{
			/** @var DOMElement $license */

			$licenseId = false;

			foreach ($license->lastChild->getElementsByTagName('a') as $downloadLink)
			{
				$href = $downloadLink->attributes->getNamedItem('href')->textContent;

				// Download link in format: customers/download/?l=<license_id>&d=<product>
				$regex = sprintf('#^.*\?l\=([A-Z0-9]+)\&d=%s.*$#', $type);
				if (!preg_match($regex, $href, $matches))
				{
					continue;
				}
				else
				{
					$licenseId = $matches[1];
				}
			}

			if (!$licenseId)
			{
				continue;
			}

			$anchors = $license->childNodes->item(1)->childNodes->item(1)->getElementsByTagName('a');

			if (!$anchors->length)
			{
				continue;
			}

			$licenses[$licenseId] = $anchors->item(0)->nodeValue;
		}

		$cookies = $client->getCookieJar()->getAllCookies(Zend_Http_CookieJar::COOKIE_STRING_CONCAT);

		return $licenses;
	}

	protected function _getVersions($cookies, $licenseId, $type = 'xenforo')
	{
		$client = XenForo_Helper_Http::getClient("https://xenforo.com/customers/download/", array(
			'useragent' => 'XenForo CLI Updater (by Liam W)'
		));

		$cookieJar = new Zend_Http_CookieJar();
		$cookieJar->addCookie($cookies, 'https://xenforo.com/customers');

		$client->setCookieJar($cookieJar);
		$client->setParameterGet('l', $licenseId);
		$client->setParameterGet('d', $type);

		$downloadForm = $client->request('GET');
		$downloadQuery = new Zend_Dom_Query($downloadForm->getBody());

		$versionsQuery = $downloadQuery->query('select[name~="download_version_id"] option');

		if (!$versionsQuery->count())
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('liam_xenforoupdater_no_downloads')));
		}

		$downloadVersions = array();

		foreach ($versionsQuery as $version)
		{
			$downloadVersions[$version->getAttribute('value')] = array(
				'value' => $version->getAttribute('value'),
				'label' => $version->textContent,
				'selected' => $version->getAttribute('selected') == 'selected' && ($type != 'xenforo' || (substr(XenForo_Application::$versionId,
								5, 1) == 7 || substr(XenForo_Application::$versionId, 5, 1) == 9))
			);
		}

		krsort($downloadVersions);

		return $downloadVersions;
	}

	protected function _downloadAndCopyZip($cookies, $downloadVersionId, $licenseId, $type = 'xenforo')
	{
		$client = XenForo_Helper_Http::getClient("https://xenforo.com/customers/download/", array(
			'useragent' => 'XenForo CLI Updater (by Liam W)'
		));

		$streamDir = XenForo_Helper_File::getInternalDataPath() . '/xf_update/';
		$streamFile = $streamDir . $downloadVersionId . '.zip';

		if (!XenForo_Helper_File::createDirectory($streamDir))
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_unable_to_create_directory'));
		}

		$cookieJar = new Zend_Http_CookieJar();
		$cookieJar->addCookie($cookies, 'https://xenforo.com/customers');

		$client->setCookieJar($cookieJar);

		$client->setStream($streamFile);
		$client->setParameterPost('download_version_id', $downloadVersionId);
		if ($type == 'xenforo')
		{
			$client->setParameterPost('options[upgradePackage]', 1);
		}
		$client->setParameterPost('agree', 1);
		$client->setParameterPost('l', $licenseId);
		$client->setParameterPost('d', $type);

		$client->request('POST');

		XenForo_Helper_File::createDirectory($streamDir . $downloadVersionId . '/');

		$zip = new Zend_Filter_Decompress(array(
			'adapter' => 'Zip',
			'options' => array(
				'target' => $streamDir . $downloadVersionId . '/'
			)
		));

		$zip->filter($streamFile);

		if (!is_dir($streamDir . $downloadVersionId . '/'))
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_unable_to_extract_zip'));
		}

		if ($this->_input->filterSingle('ftp_upload', XenForo_Input::BOOLEAN))
		{
			$ftpData = $this->_input->filter(array(
				'host' => XenForo_Input::STRING,
				'port' => XenForo_Input::INT,
				'user' => XenForo_Input::STRING,
				'password' => XenForo_Input::STRING,
				'ssl' => XenForo_Input::BOOLEAN,
				'xf_path' => XenForo_Input::STRING
			));

			if (empty($ftpData['host']))
			{
				$ftpData['host'] = '127.0.0.1';
			}
			if (empty($ftpData['port']))
			{
				$ftpData['port'] = 21;
			}
			if (empty($ftpData['xf_path']))
			{
				$ftpData['xf_path'] = 'public_html';
			}

			try
			{
				$ftp = new LiamW_XenForoUpdater_FtpClient_FtpClient();
				$ftp->connect($ftpData['host'], $ftpData['ssl'], $ftpData['port']);
				$ftp->login($ftpData['user'], $ftpData['password']);

				$ftp->putAll($streamDir . $downloadVersionId . '/upload', $ftpData['xf_path']);
			} catch (Exception $e)
			{
				XenForo_Error::logException($e);

				return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_ftp_upload_failed'));
			}
		}
		else
		{
			LiamW_XenForoUpdater_Helper::recursiveCopy($streamDir . $downloadVersionId . '/upload',
				XenForo_Application::getInstance()->getRootDir());
		}
	}
}

if (false)
{
	class XFCP_LiamW_XenForoUpdater_Extend_ControllerAdmin_Tools extends XenForo_ControllerAdmin_Tools
	{
	}
}