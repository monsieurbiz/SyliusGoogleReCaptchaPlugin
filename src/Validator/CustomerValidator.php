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

use MonsieurBiz\SyliusAntiSpamPlugin\Helper\StringHelper;
use Sylius\Component\Core\Model\CustomerInterface;

final class CustomerValidator implements ValidatorInterface
{
    public const STRING_MINIMUM_SCORE = '0.7';

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @TODO implement
     */
    public function isEligible(object $object, array $options = []): bool
    {
        return $object instanceof CustomerInterface;
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

        if (!$object instanceof CustomerInterface) {
            $errors[] = 'monsieurbiz_anti_spam.error.object_not_customer';

            return $errors;
        }

        // Check customer first name
        $firstNameHelper = new StringHelper((string) $object->getFirstName());
        if ($firstNameHelper->score < self::STRING_MINIMUM_SCORE) {
            $errors[] = 'monsieurbiz_anti_spam.error.customer_first_name_invalid';
        }
        // Check customer last name
        $lastNameHelper = new StringHelper((string) $object->getLastName());
        if ($lastNameHelper->score < self::STRING_MINIMUM_SCORE) {
            $errors[] = 'monsieurbiz_anti_spam.error.customer_last_name_invalid';
        }

        return $errors;
    }
}
