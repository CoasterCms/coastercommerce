<?php
namespace CoasterCommerce\Core\ReCaptcha;

use CoasterCommerce\Core\Model\Setting;

/**
 * Helper for recaptcha
 */
class V3
{

    /**
     * @param string $action
     * @param string $response
     * @param float $threshold
     * @return bool
     */
    public function isValid($response, $action = 'contact', $threshold = 0.5)
    {
        if (!Setting::getValue('recaptcha_secret_key')) {
            return true;
        }
        $resp = $this->_recaptcha()->setExpectedHostname(parse_url(config('app.url'), PHP_URL_HOST))
            ->setExpectedAction($action)
            ->setScoreThreshold($threshold)
            ->verify($response);
        return $resp->isSuccess();
    }

    /**
     * @return \ReCaptcha\ReCaptcha
     */
    protected function _recaptcha()
    {
        return new \ReCaptcha\ReCaptcha(
            Setting::getValue('recaptcha_secret_key')
        );
    }

}
