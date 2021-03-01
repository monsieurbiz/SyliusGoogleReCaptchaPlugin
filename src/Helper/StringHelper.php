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

namespace MonsieurBiz\SyliusAntiSpamPlugin\Helper;

use Exception;

final class StringHelper
{
    public string $string = '';
    public int $numberOfCharacters = 0;
    public int $numberOfLetters = 0;
    public int $numberOfCapitalLetters = 0;
    public int $numberOfSmallLetters = 0;
    public int $numberOfConsonants = 0;
    public int $numberOfVoyels = 0;
    public int $numberOfNumericCharacters = 0;
    public int $numberOfNonAlphanumericCharacters = 0;
    public int $numberOfSpecialCharacters = 0;
    public float $score = 0.0;
    public int $points = 0;
    public int $maxPoints = 0;

    public int $maxNumberOfConsecutiveCapitalLetters = 0;
    public int $maxNumberOfConsecutiveSmallLetters = 0;
    public int $maxNumberOfConsecutiveConsonants = 0;
    public int $maxNumberOfConsecutiveVoyels = 0;
    public int $maxNumberOfConsecutiveNumericCharacters = 0;
    public int $maxNumberOfConsecuviteNonAlphanumericCharacters = 0;
    public int $maxNumberOfConsecuviteSpecialCharacters = 0;

    private int $currentNumberOfConsecutiveCapitalLetters = 0;
    private int $currentNumberOfConsecutiveSmallLetters = 0;
    private int $currentNumberOfConsecutiveConsonants = 0;
    private int $currentNumberOfConsecutiveVoyels = 0;
    private int $currentNumberOfConsecutiveNumericCharacters = 0;
    private int $currentNumberOfConsecutiveNonAlphanumericCharacters = 0;
    private int $currentNumberOfConsecuviteSpecialCharacters = 0;

    /**
     * @param string $string
     */
    public function __construct(string $string)
    {
        $this->string = $string;
        foreach (str_split($string) as $character) {
            $this->manageCharacter($character);
        }
        $this->calculateScore();
    }

    /**
     * Check a given character to update string statistics.
     *
     * @param string $character
     *
     * @return void
     */
    private function manageCharacter(string $character): void
    {
        if (mb_strlen($character, 'UTF-8') > 1) {
            throw new Exception(sprintf('The string "%s" is not a single character', $character));
        }
        ++$this->numberOfCharacters;

        // Character is a letter
        if (ctype_alpha($character)) {
            $this->manageAlphabeticCharacter($character);

            return;
        }

        // Character is a number
        if (is_numeric($character)) {
            $this->manageNumericCharacter($character);

            return;
        }

        // Character is a non alpha-numeric
        $this->manageNonAlphanumericCharacter($character);
    }

    /**
     * Update statistics for an alphabetic character.
     *
     * @param string $character
     *
     * @return void
     */
    private function manageAlphabeticCharacter(string $character): void
    {
        if (mb_strlen($character, 'UTF-8') > 1 && !ctype_alpha($character)) {
            throw new Exception(sprintf('The string "%s" is not a single alphabetic character', $character));
        }

        // Reset what we need to reset
        $this->resetNumericCount();
        $this->resetNonAlphanumericCount();

        ++$this->numberOfLetters;

        // Check if letter is a voyel
        if (preg_match('/^[aeiouyAEIOUYàèìòùÀÈÌÒÙáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛäëïöüÄËÏÖÜ]*$/i', $character)) {
            ++$this->numberOfVoyels;
            $this->currentNumberOfConsecutiveConsonants = 0;
            ++$this->currentNumberOfConsecutiveVoyels;
            if ($this->currentNumberOfConsecutiveVoyels > $this->maxNumberOfConsecutiveVoyels) {
                $this->maxNumberOfConsecutiveVoyels = $this->currentNumberOfConsecutiveVoyels;
            }
        } else {
            ++$this->numberOfConsonants;
            $this->currentNumberOfConsecutiveVoyels = 0;
            ++$this->currentNumberOfConsecutiveConsonants;
            if ($this->currentNumberOfConsecutiveConsonants > $this->maxNumberOfConsecutiveConsonants) {
                $this->maxNumberOfConsecutiveConsonants = $this->currentNumberOfConsecutiveConsonants;
            }
        }

        // Check if letter is a capital
        if (ctype_upper($character)) {
            ++$this->numberOfCapitalLetters;
            $this->currentNumberOfConsecutiveSmallLetters = 0;
            ++$this->currentNumberOfConsecutiveCapitalLetters;
            if ($this->currentNumberOfConsecutiveCapitalLetters > $this->maxNumberOfConsecutiveCapitalLetters) {
                $this->maxNumberOfConsecutiveCapitalLetters = $this->currentNumberOfConsecutiveCapitalLetters;
            }
        } else {
            ++$this->numberOfSmallLetters;
            $this->currentNumberOfConsecutiveCapitalLetters = 0;
            ++$this->currentNumberOfConsecutiveSmallLetters;
            if ($this->currentNumberOfConsecutiveSmallLetters > $this->maxNumberOfConsecutiveSmallLetters) {
                $this->maxNumberOfConsecutiveSmallLetters = $this->currentNumberOfConsecutiveSmallLetters;
            }
        }
    }

    /**
     * Update statistics for a numeric character.
     *
     * @param string $character
     *
     * @return void
     */
    private function manageNumericCharacter(string $character): void
    {
        if (mb_strlen($character, 'UTF-8') > 1 && !is_numeric($character)) {
            throw new Exception(sprintf('The string "%s" is not a single numeric character', $character));
        }

        // Reset what we need to reset
        $this->resetAlphabeticCount();
        $this->resetNonAlphanumericCount();

        ++$this->numberOfNumericCharacters;
        ++$this->currentNumberOfConsecutiveNumericCharacters;
        if ($this->currentNumberOfConsecutiveNumericCharacters > $this->maxNumberOfConsecutiveNumericCharacters) {
            $this->maxNumberOfConsecutiveNumericCharacters = $this->currentNumberOfConsecutiveNumericCharacters;
        }
    }

    /**
     * Update statistics for a non-alphanumeric character.
     *
     * @param string $character
     *
     * @return void
     */
    private function manageNonAlphanumericCharacter(string $character): void
    {
        if (mb_strlen($character, 'UTF-8') > 1 && ctype_alnum($character)) {
            throw new Exception(sprintf('The string "%s" is not a single non-alphanumeric character', $character));
        }

        // Reset what we need to reset
        $this->resetAlphabeticCount();
        $this->resetNumericCount();

        if (preg_match('/^[- \']*$/i', $character)) {
            ++$this->numberOfNonAlphanumericCharacters;
            $this->currentNumberOfConsecuviteSpecialCharacters = 0;
            ++$this->currentNumberOfConsecutiveNonAlphanumericCharacters;
            if ($this->currentNumberOfConsecutiveNonAlphanumericCharacters > $this->maxNumberOfConsecuviteNonAlphanumericCharacters) {
                $this->maxNumberOfConsecuviteNonAlphanumericCharacters = $this->currentNumberOfConsecutiveNonAlphanumericCharacters;
            }
        } else {
            ++$this->numberOfSpecialCharacters;
            $this->currentNumberOfConsecutiveNonAlphanumericCharacters = 0;
            ++$this->currentNumberOfConsecuviteSpecialCharacters;
            if ($this->currentNumberOfConsecuviteSpecialCharacters > $this->maxNumberOfConsecuviteSpecialCharacters) {
                $this->maxNumberOfConsecuviteSpecialCharacters = $this->currentNumberOfConsecuviteSpecialCharacters;
            }
        }
    }

    /**
     * Reset alphabetic count.
     *
     * @return void
     */
    private function resetAlphabeticCount(): void
    {
        $this->currentNumberOfConsecutiveCapitalLetters = 0;
        $this->currentNumberOfConsecutiveSmallLetters = 0;
        $this->currentNumberOfConsecutiveVoyels = 0;
        $this->currentNumberOfConsecutiveConsonants = 0;
    }

    /**
     * Reset numeric count.
     *
     * @return void
     */
    private function resetNumericCount(): void
    {
        $this->currentNumberOfConsecutiveNumericCharacters = 0;
    }

    /**
     * Reset non alpha-numeric count.
     *
     * @return void
     */
    private function resetNonAlphanumericCount(): void
    {
        $this->currentNumberOfConsecutiveNonAlphanumericCharacters = 0;
        $this->currentNumberOfConsecuviteSpecialCharacters = 0;
    }

    /**
     * Process a score to check if string seems to be human readable or not.
     *
     * @return void
     */
    private function calculateScore(): void
    {
        $points = 0;
        $maxPoints = 0;

        // Non weird-capital letter
        if (
            $this->numberOfCapitalLetters === $this->numberOfLetters
            || $this->numberOfCapitalLetters < 3
        ) {
            $points += 50;
        }
        $maxPoints += 50;

        // Check capitalized string
        if ($this->string === ucwords(mb_strtolower($this->string), '- \'') || 0 === $this->numberOfCapitalLetters) {
            $points += 50;
        }
        $maxPoints += 50;

        // Non weird consecutive of capital letters
        if ($this->maxNumberOfConsecutiveCapitalLetters <= 2) {
            $points += (3 - $this->maxNumberOfConsecutiveCapitalLetters) * 50;
        }
        $maxPoints += (3 - 0) * 50;

        // Non weird consecutive of consonants letters
        if ($this->maxNumberOfConsecutiveConsonants <= 4) {
            $points += (5 - $this->maxNumberOfConsecutiveConsonants) * 10;
        }
        $maxPoints += (5 - 0) * 10;

        // Non weird consecutive of voyels letters
        if ($this->maxNumberOfConsecutiveVoyels <= 4) {
            $points += (5 - $this->maxNumberOfConsecutiveConsonants) * 10;
        }
        $maxPoints += (5 - 0) * 10;

        // Non alphanumeric characters are better
        if (0 === $this->numberOfSpecialCharacters) {
            $points += 30;
        }
        $maxPoints += 30;

        $this->points = $points;
        $this->maxPoints = $maxPoints;
        $this->score = round($points / $maxPoints, 2);
    }
}
