<?php

namespace App\Services\Yaml;

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfYaml offers convenience methods to load and dump YAML.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version    SVN: $Id: sfYaml.class.php 8988 2008-05-15 20:24:26Z fabien $
 */
class sfYaml
{
    protected static $spec = '1.2';

    /**
     * Sets the YAML specification version to use.
     *
     * @param string $version The YAML specification version
     */
    public static function setSpecVersion($version)
    {
        if (!in_array($version, ['1.1', '1.2'])) {
            throw new InvalidArgumentException(sprintf('Version %s of the YAML specifications is not supported', $version));
        }

        self::$spec = $version;
    }

    /**
     * Gets the YAML specification version to use.
     *
     * @return string The YAML specification version
     */
    public static function getSpecVersion()
    {
        return self::$spec;
    }

    /**
     * Loads YAML into a PHP array.
     *
     * The load method, when supplied with a YAML stream (string or file),
     * will do its best to convert YAML in a file into a PHP array.
     *
     *  Usage:
     *  <code>
     *   $array = sfYaml::load('config.yml');
     *   print_r($array);
     *  </code>
     *
     * @param string $input Path of YAML file or string containing YAML
     *
     * @throws InvalidArgumentException If the YAML is not valid
     *
     * @return array The YAML converted to a PHP array
     */
    public static function load($input)
    {
        $file = '';

        // if input is a file, process it
        if (false === strpos($input, "\n") && is_file($input)) {
            $file = $input;

            ob_start();
            $retval = include $input;
            $content = ob_get_clean();

            // if an array is returned by the config file assume it's in plain php form else in YAML
            $input = is_array($retval) ? $retval : $content;
        }

        // if an array is returned by the config file assume it's in plain php form else in YAML
        if (is_array($input)) {
            return $input;
        }

        require_once __DIR__.'/sfYamlParser.php';

        $yaml = new sfYamlParser();

        try {
            $ret = $yaml->parse($input);
        } catch (Exception $e) {
            throw new InvalidArgumentException(sprintf('Unable to parse %s: %s', $file ? sprintf('file "%s"', $file) : 'string', $e->getMessage()));
        }

        return $ret;
    }

    /**
     * Dumps a PHP array to a YAML string.
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.
     *
     * @param array $array  PHP array
     * @param int   $inline The level where you switch to inline YAML
     *
     * @return string A YAML string representing the original PHP array
     */
    public static function dump($array, $inline = 2)
    {
        require_once __DIR__.'/sfYamlDumper.php';

        $yaml = new sfYamlDumper();

        return $yaml->dump($array, $inline);
    }
}

/**
 * Wraps echo to automatically provide a newline.
 *
 * @param string $string The string to echo with new line
 */
function echoln($string)
{
    echo $string."\n";
}