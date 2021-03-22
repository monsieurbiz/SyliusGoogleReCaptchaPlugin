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
        // Reset what we need to reset
        $this->resetNumericCount();
        $this->resetNonAlphanumericCount();

        ++$this->numberOfLetters;

        // Check if letter is a voyel
        $this->manageTypeOfCharacter($character);

        // Check if letter is a capital
        $this->manageCaseOfCharacter($character);
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

        $this->updateNumericCharacter();
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
        // Reset what we need to reset
        $this->resetAlphabeticCount();
        $this->resetNumericCount();

        // List of authorized characters
        preg_match('/^[- \']*$/i', $character) ? $this->updateNonAlphanumericCharacter() : $this->updateSpecialCharacter();
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
     * Manage voyel and consonant characters.
     *
     * @param string $character
     *
     * @return void
     */
    private function manageTypeOfCharacter(string $character): void
    {
        preg_match('/^[aeiouyAEIOUYàèìòùÀÈÌÒÙáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛäëïöüÄËÏÖÜ]*$/i', $character) ?
            $this->updateVoyelCharacter() : $this->updateConsonantCharacter();
    }

    /**
     * Manage capital and small character.
     *
     * @param string $character
     *
     * @return void
     */
    private function manageCaseOfCharacter(string $character): void
    {
        ctype_upper($character) ? $this->updateCapitalCharacter() : $this->updateSmallCharacter();
    }

    /**
     * Update counters for a small character.
     *
     * @return void
     */
    private function updateSmallCharacter(): void
    {
        ++$this->numberOfSmallLetters;
        $this->currentNumberOfConsecutiveCapitalLetters = 0;
        ++$this->currentNumberOfConsecutiveSmallLetters;
        if ($this->currentNumberOfConsecutiveSmallLetters > $this->maxNumberOfConsecutiveSmallLetters) {
            $this->maxNumberOfConsecutiveSmallLetters = $this->currentNumberOfConsecutiveSmallLetters;
        }
    }

    /**
     * Update counters for a capital character.
     *
     * @return void
     */
    private function updateCapitalCharacter(): void
    {
        ++$this->numberOfCapitalLetters;
        $this->currentNumberOfConsecutiveSmallLetters = 0;
        ++$this->currentNumberOfConsecutiveCapitalLetters;
        if ($this->currentNumberOfConsecutiveCapitalLetters > $this->maxNumberOfConsecutiveCapitalLetters) {
            $this->maxNumberOfConsecutiveCapitalLetters = $this->currentNumberOfConsecutiveCapitalLetters;
        }
    }

    /**
     * Update counters for a voyel character.
     *
     * @return void
     */
    private function updateVoyelCharacter(): void
    {
        ++$this->numberOfVoyels;
        $this->currentNumberOfConsecutiveConsonants = 0;
        ++$this->currentNumberOfConsecutiveVoyels;
        if ($this->currentNumberOfConsecutiveVoyels > $this->maxNumberOfConsecutiveVoyels) {
            $this->maxNumberOfConsecutiveVoyels = $this->currentNumberOfConsecutiveVoyels;
        }
    }

    /**
     * Update counters for a consonant character.
     *
     * @return void
     */
    private function updateConsonantCharacter(): void
    {
        ++$this->numberOfVoyels;
        $this->currentNumberOfConsecutiveConsonants = 0;
        ++$this->currentNumberOfConsecutiveVoyels;
        if ($this->currentNumberOfConsecutiveVoyels > $this->maxNumberOfConsecutiveVoyels) {
            $this->maxNumberOfConsecutiveVoyels = $this->currentNumberOfConsecutiveVoyels;
        }
    }

    /**
     * Update counters for a numeric character.
     *
     * @return void
     */
    private function updateNumericCharacter(): void
    {
        ++$this->numberOfNumericCharacters;
        ++$this->currentNumberOfConsecutiveNumericCharacters;
        if ($this->currentNumberOfConsecutiveNumericCharacters > $this->maxNumberOfConsecutiveNumericCharacters) {
            $this->maxNumberOfConsecutiveNumericCharacters = $this->currentNumberOfConsecutiveNumericCharacters;
        }
    }

    /**
     * Update counters for a non alpha-numeric character.
     *
     * @return void
     */
    private function updateNonAlphanumericCharacter(): void
    {
        ++$this->numberOfNonAlphanumericCharacters;
        $this->currentNumberOfConsecuviteSpecialCharacters = 0;
        ++$this->currentNumberOfConsecutiveNonAlphanumericCharacters;
        if ($this->currentNumberOfConsecutiveNonAlphanumericCharacters > $this->maxNumberOfConsecuviteNonAlphanumericCharacters) {
            $this->maxNumberOfConsecuviteNonAlphanumericCharacters = $this->currentNumberOfConsecutiveNonAlphanumericCharacters;
        }
    }

    /**
     * Update counters for a special character.
     *
     * @return void
     */
    private function updateSpecialCharacter(): void
    {
        ++$this->numberOfSpecialCharacters;
        $this->currentNumberOfConsecutiveNonAlphanumericCharacters = 0;
        ++$this->currentNumberOfConsecuviteSpecialCharacters;
        if ($this->currentNumberOfConsecuviteSpecialCharacters > $this->maxNumberOfConsecuviteSpecialCharacters) {
            $this->maxNumberOfConsecuviteSpecialCharacters = $this->currentNumberOfConsecuviteSpecialCharacters;
        }
    }

    /**
     * Process a score to check if string seems to be human readable or not.
     *
     * @return void
     */
    private function calculateScore(): void
    {
        $this->addCapitalLettersScore();
        $this->addCapitalizeScore();
        $this->addConsecutiveCapitalLettersScore();
        $this->addConsonantLettersScore();
        $this->addVoyelLettersScore();
        $this->addSpecialCharactersScore();

        $this->score = round($this->points / $this->maxPoints, 2);
    }

    /**
     * Check if the string has got a coherent number of capital letters
     * Good example : `Monsieur Biz`
     * Bad example : `MoNsIeUr BiZ`.
     *
     * @return void
     */
    private function addCapitalLettersScore(): void
    {
        // Non weird number of capital letter
        if (
            $this->numberOfCapitalLetters === $this->numberOfLetters
            || $this->numberOfCapitalLetters < 3
        ) {
            $this->points += 50;
        }
        $this->maxPoints += 50;
    }

    /**
     * Check if the string has got a coherent capital letters location
     * Good examples : `Monsieur Biz`, `monsieur biz`, `MONSIEUR BIZ`
     * Bad example : `MoNsIeUr BiZ`.
     *
     * @return void
     */
    private function addCapitalizeScore(): void
    {
        // Check capitalized string
        if (
            $this->string === ucwords(mb_strtolower($this->string), '- \'')
            || 0 === $this->numberOfCapitalLetters
            || $this->numberOfLetters === $this->numberOfCapitalLetters
        ) {
            $this->points += 50;
        }
        $this->maxPoints += 50;
    }

    /**
     * Check if the string has got a coherent consecutive capital letters
     * Good examples : `Monsieur Biz`,
     * Bad example : `MoNsIeUr BiZ`.
     *
     * @return void
     */
    private function addConsecutiveCapitalLettersScore(): void
    {
        // Non weird consecutive of capital letters
        if ($this->maxNumberOfConsecutiveCapitalLetters <= 2) {
            $this->points += (3 - $this->maxNumberOfConsecutiveCapitalLetters) * 50;
        }
        $this->maxPoints += 150;
    }

    /**
     * Check if the string has got a coherent consecutive capital letters
     * Good examples : `Monsieur Biz`,
     * Bad example : `Mrbz`.
     *
     * @return void
     */
    private function addConsonantLettersScore(): void
    {
        // Non weird consecutive of consonants letters
        if ($this->maxNumberOfConsecutiveConsonants <= 4) {
            $this->points += (5 - $this->maxNumberOfConsecutiveConsonants) * 10;
        }
        $this->maxPoints += 50;
    }

    /**
     * Check if the string has got a coherent consecutive capital letters
     * Good examples : `Monsieur Biz`,
     * Bad example : `Moieur Biz`.
     *
     * @return void
     */
    private function addVoyelLettersScore(): void
    {
        // Non weird consecutive of voyels letters
        if ($this->maxNumberOfConsecutiveVoyels <= 4) {
            $this->points += (5 - $this->maxNumberOfConsecutiveConsonants) * 10;
        }
        $this->maxPoints += 50;
    }

    /**
     * Check if the string has got a coherent consecutive capital letters
     * Good examples : `Monsieur Biz`,
     * Bad example : `Monsieur#Biz`.
     *
     * @return void
     */
    private function addSpecialCharactersScore(): void
    {
        // Non special characters are better
        if (0 === $this->numberOfSpecialCharacters) {
            $this->points += 30;
        }
        $this->maxPoints += 30;
    }
}
