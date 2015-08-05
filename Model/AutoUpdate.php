<?php

class LiamW_XenForoUpdater_Model_AutoUpdate extends XenForo_Model
{
	const LOGIN_URL = 'https://xenforo.com/customers/login';
	const CUSTOMER_URL = 'https://xenforo.com/customers';
	const DOWNLOAD_URL = 'https://xenforo.com/customers/download';
	const CUSTOMER_REDIRECT = 'customers';

	const USER_AGENT = 'XenForo Updater (Liam W)';

	const PRODUCT_XENFORO = 'xenforo';
	const PRODUCT_RESOURCE_MANAGER = 'xfresource';
	const PRODUCT_MEDIA_GALLERY = 'xfmg';
	const PRODUCT_ENHANCED_SEARCH = 'xfes';

	public function getAvailableProducts()
	{
		$activeAddons = XenForo_Application::get('addOns');

		$availableProducts = array(self::PRODUCT_XENFORO);

		if (isset($activeAddons['XenResource']))
		{
			$availableProducts[] = self::PRODUCT_RESOURCE_MANAGER;
		}
		if (isset($activeAddons['XenGallery']))
		{
			$availableProducts[] = self::PRODUCT_MEDIA_GALLERY;
		}
		if (isset($activeAddons['XenES']))
		{
			$availableProducts[] = self::PRODUCT_ENHANCED_SEARCH;
		}

		return $availableProducts;
	}

	public function getLicenses($email, $password, &$cookies, $product = self::PRODUCT_XENFORO)
	{
		$this->_assertValidProduct($product);

		$client = XenForo_Helper_Http::getClient(self::LOGIN_URL, array(
			'useragent' => self::USER_AGENT
		));
		if (empty($cookies))
		{
			$client->setCookieJar();
		}
		else
		{
			$cookieJar = new Zend_Http_CookieJar();
			$cookieJar->addCookie($cookies, 'https://xenforo.com/customers');

			$client->setCookieJar($cookieJar);
		}
		$client->setParameterPost('email', $email);
		$client->setParameterPost('password', $password);
		$client->setParameterPost('redirect', self::CUSTOMER_REDIRECT);

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
				$regex = sprintf('#^.*\?l\=([A-Z0-9]+)\&d=%s.*$#', $product);
				if (preg_match($regex, $href, $matches))
				{
					$licenseId = $matches[1];
				}
			}

			if (!$licenseId)
			{
				// No license for requested product found on this license, continue onto next license.
				continue;
			}

			$anchors = $license->childNodes->item(1)->childNodes->item(1)->getElementsByTagName('a');

			if (!$anchors->length)
			{
				// License hasn't been named - isn't valid.
				continue;
			}

			$licenses[$licenseId] = $anchors->item(0)->nodeValue;
		}

		$cookies = empty($cookies) ? $client->getCookieJar()
			->getAllCookies(Zend_Http_CookieJar::COOKIE_STRING_CONCAT) : $cookies;

		return $licenses;
	}

	public function getVersions($cookies, $licenseId, $product = self::PRODUCT_XENFORO)
	{
		$client = XenForo_Helper_Http::getClient(self::DOWNLOAD_URL, array(
			'useragent' => self::USER_AGENT
		));

		$cookieJar = new Zend_Http_CookieJar();
		$cookieJar->addCookie($cookies, 'https://xenforo.com/customers');

		$client->setCookieJar($cookieJar);
		$client->setParameterGet('l', $licenseId);
		$client->setParameterGet('d', $product);

		$downloadForm = $client->request('GET');
		$downloadQuery = new Zend_Dom_Query($downloadForm->getBody());

		$versionsQuery = $downloadQuery->query('select[name~="download_version_id"] option');

		if (!$versionsQuery->count())
		{
			return false;
		}

		$downloadVersions = array();

		foreach ($versionsQuery as $version)
		{
			$downloadVersions[$version->getAttribute('value')] = array(
				'value' => $version->getAttribute('value'),
				'label' => $version->textContent,
				'selected' => $version->getAttribute('selected') == 'selected' && ($product != self::PRODUCT_XENFORO || (substr(XenForo_Application::$versionId,
								5, 1) == 7 || substr(XenForo_Application::$versionId, 5, 1) == 9))
			);
		}

		krsort($downloadVersions);

		return $downloadVersions;
	}

	public function downloadAndCopy($cookies, $downloadVersionId, $licenseId, array $ftpData, $product = self::PRODUCT_XENFORO)
	{
		$client = XenForo_Helper_Http::getClient(self::DOWNLOAD_URL, array(
			'useragent' => self::USER_AGENT
		));

		$streamDir = XenForo_Helper_File::getInternalDataPath() . '/xf_update/';
		$streamFile = $streamDir . $downloadVersionId . '.zip';

		if (!XenForo_Helper_File::createDirectory($streamDir))
		{
			return false;
		}

		$cookieJar = new Zend_Http_CookieJar();
		$cookieJar->addCookie($cookies, 'https://xenforo.com/customers');

		$client->setCookieJar($cookieJar);

		$client->setStream($streamFile);
		$client->setParameterPost('download_version_id', $downloadVersionId);
		if ($product == self::PRODUCT_XENFORO)
		{
			$client->setParameterPost('options[upgradePackage]', 1);
		}
		$client->setParameterPost('agree', 1);
		$client->setParameterPost('l', $licenseId);
		$client->setParameterPost('d', $product);

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
			return false;
		}

		if ($ftpData['ftp_upload'])
		{
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

				return false;
			}
		}
		else
		{
			LiamW_XenForoUpdater_Helper::recursiveCopy($streamDir . $downloadVersionId . '/upload',
				XenForo_Application::getInstance()->getRootDir());
		}

		return true;
	}

	protected function _assertValidProduct($product)
	{
		switch ($product)
		{
			case self::PRODUCT_XENFORO:
			case self::PRODUCT_RESOURCE_MANAGER:
			case self::PRODUCT_MEDIA_GALLERY:
			case self::PRODUCT_ENHANCED_SEARCH:
				return;
			default:
				throw new XenForo_Exception('Invalid product passed to Update model!');
		}
	}
}
