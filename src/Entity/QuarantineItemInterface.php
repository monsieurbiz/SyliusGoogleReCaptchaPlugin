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

namespace MonsieurBiz\SyliusAntiSpamPlugin\Entity;

use DateTimeInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface QuarantineItemInterface extends ResourceInterface
{
    public const LEVEL_PROVEN = 16;
    public const LEVEL_LIKELY = 8;
    public const LEVEL_SUSPECTED = 4;

    /**
     * @return string[]|null
     */
    public function getReasonCodes(): ?array;

    /**
     * @param string[]|null $reasonCodes
     */
    public function setReasonCodes(?array $reasonCodes): void;

    /**
     * @return int|null
     */
    public function getLevel(): ?int;

    /**
     * @param int|null $level
     */
    public function setLevel(?int $level): void;

    /**
     * @return string|null
     */
    public function getEmail(): ?string;

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void;

    /**
     * @return DateTimeInterface|null
     */
    public function getLiftedAt(): ?DateTimeInterface;

    /**
     * @param DateTimeInterface|null $liftedAt
     */
    public function setLiftedAt(?DateTimeInterface $liftedAt): void;

    /**
     * @param int|null $level
     *
     * @return bool
     */
    public function isQuarantined(?int $level = null): bool;
}
