<?php

namespace ScriptTask;

class User
{
    public string $name;
    public string $surname;
    public string $email;

    public function __construct(string $name, string $surname, string $email)
    {
        $this->name = $this->sanitizeName($name);
        $this->surname = $this->sanitizeName($surname);
        $this->email = $this->sanitizeEmail($email);
    }

    /**
     * Sanitize the given (partial) name of a person
     *
     * - removes all characters except a-zA-Z ,.'-
     * - re-capitalize, take last name inserts into account
     * - remove excess white spaces
     *
     * Inspired from: https://timvisee.com/blog/snippet-correctly-capitalize-names-in-php
     *
     * @param string $name The input name
     * @return string The normalized name
     */
    public function sanitizeName(string $name): string
    {
        // A list of strings that are considered special
        $CASED = array(
            "O'", "l'", "d'", 'St.', 'Mc', 'the', 'van', 'het', 'in', "'t", 'ten',
            'den', 'von', 'und', 'der', 'de', 'da', 'of', 'and', 'the', 'III', 'IV',
            'VI', 'VII', 'VIII', 'IX'
        );
        
        // Remove all characters except a-zA-Z ,.'-
        $name = preg_replace("/[^a-zA-Z ,.'-]/", '', $name);

        // Trim whitespace sequences to one space, append space to properly chunk
        $name = preg_replace('/\s+/', ' ', $name) . ' ';

        // Break name up into parts split by name separators
        $parts = preg_split('/( |-|O\'|l\'|d\'|St\\.|Mc)/i', $name, -1, PREG_SPLIT_DELIM_CAPTURE);

        // Chunk parts, use $CASED or uppercase first, remove unfinished chunks
        $parts = array_chunk($parts, 2);
        $parts = array_filter($parts, function ($part) {
            return sizeof($part) == 2;
        });

        $parts = array_map(function ($part) use ($CASED) {
            // Extract to name and separator part
            list($name, $separator) = $part;

            // Use specified case for separator if set
            $cased = current(array_filter($CASED, function ($i) use ($separator) {
                return strcasecmp($i, $separator) == 0;
            }));
            $separator = $cased ? $cased : $separator;

            // Choose specified part case, or uppercase first as default
            $cased = current(array_filter($CASED, function ($i) use ($name) {
                return strcasecmp($i, $name) == 0;
            }));
            return [$cased ? $cased : ucfirst(strtolower($name)), $separator];
        }, $parts);
        $parts = array_map(function ($part) {
            return implode($part);
        }, $parts);
        $name = implode($parts);

        // Trim and return normalized name
        return trim($name);
    }

    /**
     * Sanitize the given email
     * 
     * - characters are set to lowercase
     * - remove all characters except letters, digits and !#$%&'*+-=?^_`{|}~@.[]. 
     * 
     * @param string $email The input email
     * @return string The sanitized email
     */
    public function sanitizeEmail(string $email): string
    {
        $email = strtolower($email);
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Validates a given (partion) name of a person
     * 
     * @param string $name The input name
     * @return bool
     */
    public function validateName(string $name): bool
    {
        return preg_match("/^[a-zA-Z ,.\'-]+$/", $name);
    }

     /**
     * Validates a given email
     * 
     * @param string $email The input email
     * @return bool
     */
    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
