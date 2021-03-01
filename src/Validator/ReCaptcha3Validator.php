<?php

/*
 * This file is part of Monsieur Biz' Anti Spam plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusAntiSpamPlugin\Validator;

use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3Validator as Recaptcha3ValidatorConstraints;
use ReCaptcha\Response as RecaptchaResponse;

final class ReCaptcha3Validator implements ValidatorInterface
{
    const CAPTCHA_MINIMUM_SCORE = 0.8;

    /**
     * @var Recaptcha3ValidatorConstraints
     */
    private Recaptcha3ValidatorConstraints $recaptcha3ValidatorConstraint;

    /**
     * @var bool
     */
    private bool $karserRecaptcha3Enabled;

    public function __construct(
        Recaptcha3ValidatorConstraints $recaptcha3ValidatorConstraint,
        bool $karserRecaptcha3Enabled
    ) {
        $this->recaptcha3ValidatorConstraint = $recaptcha3ValidatorConstraint;
        $this->karserRecaptcha3Enabled = $karserRecaptcha3Enabled;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @TODO implement
     */
    public function isEligible(object $object, array $options = []): bool
    {
        return $this->karserRecaptcha3Enabled;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @TODO implement
     */
    public function validate(object $object, array $options = []): array
    {
        $errors = [];

        $lastResponse = $this->recaptcha3ValidatorConstraint->getLastResponse();
        if (!$lastResponse instanceof RecaptchaResponse) {
            $errors[] = 'monsieurbiz_anti_spam.error.missing_captcha_reponse';
        } elseif ($lastResponse->getScore() < self::CAPTCHA_MINIMUM_SCORE) {
            $errors[] = 'monsieurbiz_anti_spam.error.invalid_captcha_score';
        }

        return $errors;
    }
}
