<?php

class LiamW_XenForoUpdater_Extend_ControllerAdmin_Tools extends XFCP_LiamW_XenForoUpdater_Extend_ControllerAdmin_Tools
{
	public function actionUpdate()
	{
		$this->_assertCanUpdate();

		return $this->responseView('', 'liam_xenforo_update_initial');
	}

	public function actionUpdateStepProduct()
	{
		$this->_assertPostOnly();
		$this->_assertCanUpdate();

		if (!$this->isConfirmedPost())
		{
			return $this->responseNoPermission();
		}

		$availableProducts = $this->_getAutomaticUpdateModel()
			->getAvailableProducts();

		$availableProductsReturn = array();
		foreach ($availableProducts as $availableProduct)
		{
			$availableProductsReturn[$availableProduct] = new XenForo_Phrase('liam_xenforoupdater_update_' . $availableProduct);
		}

		$viewParams = array(
			'availableProducts' => $availableProductsReturn,
		);

		return $this->responseView('', 'liam_xenforo_update_product', $viewParams);
	}

	public function actionUpdateStepCredentials()
	{
		$this->_assertPostOnly();
		$this->_assertCanUpdate();

		if (!$this->isConfirmedPost())
		{
			return $this->responseNoPermission();
		}

		$viewParams = array(
			'product' => $this->_input->filterSingle('product', XenForo_Input::STRING)
		);

		$this->_assertValidProduct($viewParams['product']);

		$viewParams['productName'] = new XenForo_Phrase('liam_xenforoupdater_update_' . $viewParams['product']);

		return $this->responseView('', 'liam_xenforo_update_credentials', $viewParams);
	}

	public function actionUpdateStepLicense()
	{
		$this->_assertPostOnly();
		$this->_assertCanUpdate();

		if (!$this->isConfirmedPost())
		{
			return $this->responseNoPermission();
		}

		$data = $this->_input->filter(array(
			'email' => XenForo_Input::STRING,
			'password' => XenForo_Input::STRING,
			'product' => XenForo_Input::STRING
		));

		$this->_assertValidProduct($data['product']);

		if (!$data['email'] || !$data['password'])
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_invalid_credentials'));
		}

		$licenses = $this->_getAutomaticUpdateModel()
			->getLicenses($data['email'], $data['password'], $cookies, $data['product']);

		if (!$licenses)
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_invalid_credentials_or_no_licenses'));
		}

		$viewParams = array(
			'licenses' => $licenses,
			'cookies' => $cookies,
			'product' => $data['product']
		);

		$viewParams['productName'] = new XenForo_Phrase('liam_xenforoupdater_update_' . $viewParams['product']);


		return $this->responseView('', 'liam_xenforo_update_licenses', $viewParams);
	}

	public function actionUpdateStepVersion()
	{
		$this->_assertPostOnly();
		$this->_assertCanUpdate();

		if (!$this->isConfirmedPost())
		{
			return $this->responseNoPermission();
		}

		$data = $this->_input->filter(array(
			'license_id' => XenForo_Input::STRING,
			'cookies' => XenForo_Input::STRING,
			'product' => XenForo_Input::STRING
		));

		$this->_assertValidProduct($data['product']);

		if (!$data['cookies'])
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_cookies_missing'));
		}

		if (!$data['license_id'])
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_must_select_license'));
		}

		$downloadVersions = $this->_getAutomaticUpdateModel()
			->getVersions($data['cookies'], $data['license_id'], $data['product']);

		$viewParams = array(
			'versions' => $downloadVersions,
			'licenseId' => $data['license_id'],
			'cookies' => $data['cookies'],
			'product' => $data['product']
		);

		$viewParams['productName'] = new XenForo_Phrase('liam_xenforoupdater_update_' . $viewParams['product']);

		return $this->responseView('', 'liam_xenforo_update_version', $viewParams);
	}

	public function actionUpdateStepUpdate()
	{
		$this->_assertPostOnly();
		$this->_assertCanUpdate();

		if (!$this->isConfirmedPost())
		{
			return $this->responseNoPermission();
		}

		$data = $this->_input->filter(array(
			'download_version_id' => XenForo_Input::STRING,
			'license_id' => XenForo_Input::STRING,
			'cookies' => XenForo_Input::STRING,
			'product' => XenForo_Input::STRING
		));

		$this->_assertValidProduct($data['product']);

		if (!$data['license_id'])
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_must_select_license'));
		}

		if (!$data['cookies'])
		{
			return $this->responseError(new XenForo_Phrase('liam_xenforoupdater_cookies_missing'));
		}

		@set_time_limit(0);
		@ignore_user_abort(true);

		$ftpData = $this->_input->filter(array(
			'ftp_upload' => XenForo_Input::BOOLEAN,
			'host' => XenForo_Input::STRING,
			'port' => XenForo_Input::INT,
			'user' => XenForo_Input::STRING,
			'password' => XenForo_Input::STRING,
			'ssl' => XenForo_Input::BOOLEAN,
			'xf_path' => XenForo_Input::STRING
		));

		$result = $this->_getAutomaticUpdateModel()
			->downloadAndCopy($data['cookies'], $data['download_version_id'], $data['license_id'], $ftpData, $error,
				$data['product']);

		if (!$result)
		{
			if (!$error)
			{
				$error = new XenForo_Phrase('liam_xenforoupdater_unknown_error_occured_during_download_and_copy');
			}

			return $this->responseError($error);
		}

		switch ($data['product'])
		{
			case LiamW_XenForoUpdater_Model_AutoUpdate::PRODUCT_XENFORO:
				$boardUrl = XenForo_Application::getOptions()->boardUrl;

				return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS,
					$boardUrl . '/install/index.php?upgrade/');
				break;
			case LiamW_XenForoUpdater_Model_AutoUpdate::PRODUCT_RESOURCE_MANAGER:
				$this->_request->setParam('addon_id', 'XenResource');
				$this->_request->setParam('server_file',
					realpath(XenForo_Application::getInstance()
							->getRootDir() . '/library/XenResource/addon-XenResource.xml'));
				break;
			case LiamW_XenForoUpdater_Model_AutoUpdate::PRODUCT_MEDIA_GALLERY:
				$this->_request->setParam('addon_id', 'XenGallery');
				$this->_request->setParam('server_file',
					realpath(XenForo_Application::getInstance()
							->getRootDir() . '/library/XenGallery/addon-XenGallery.xml'));
				break;
			case LiamW_XenForoUpdater_Model_AutoUpdate::PRODUCT_ENHANCED_SEARCH:
				$this->_request->setParam('addon_id', 'XenES');
				$this->_request->setParam('server_file',
					realpath(XenForo_Application::getInstance()->getRootDir() . '/library/XenES/addon-XenES.xml'));
				break;
		}

		return $this->responseReroute('XenForo_ControllerAdmin_AddOn',
			'Upgrade'); // Use the default add-on upgrade system for the rest.
	}

	protected function _assertValidProduct($product)
	{
		$addOns = XenForo_Application::get('addOns');

		switch ($product)
		{
			case LiamW_XenForoUpdater_Model_AutoUpdate::PRODUCT_XENFORO:
				return;
			case LiamW_XenForoUpdater_Model_AutoUpdate::PRODUCT_RESOURCE_MANAGER:
				if (isset($addOns['XenResource']))
				{
					return;
				}
				break;
			case LiamW_XenForoUpdater_Model_AutoUpdate::PRODUCT_MEDIA_GALLERY:
				if (isset($addOns['XenGallery']))
				{
					return;
				}
				break;
			case LiamW_XenForoUpdater_Model_AutoUpdate::PRODUCT_ENHANCED_SEARCH:
				if (isset($addOns['XenES']))
				{
					return;
				}
				break;
		}

		throw $this->responseException($this->responseError(new XenForo_Phrase('liam_xenforoupdater_invalid_product')));
	}

	protected function _assertCanUpdate()
	{
		if (!LiamW_XenForoUpdater_Helper::zipInstalled())
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('liam_xenforoupdater_zip_extension_required')));
		}
	}

	/**
	 * @return LiamW_XenForoUpdater_Model_AutoUpdate
	 */
	protected function _getAutomaticUpdateModel()
	{
		return $this->getModelFromCache('LiamW_XenForoUpdater_Model_AutoUpdate');
	}
}

if (false)
{
	class XFCP_LiamW_XenForoUpdater_Extend_ControllerAdmin_Tools extends XenForo_ControllerAdmin_Tools
	{
	}
}