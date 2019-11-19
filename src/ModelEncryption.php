<?php

namespace CustomD\EloquentModelEncrypt;

use CustomD\EloquentModelEncrypt\Traits\Keystore;
use CustomD\EloquentModelEncrypt\Traits\Extenders;
use CustomD\EloquentModelEncrypt\Traits\Decryption;
use CustomD\EloquentModelEncrypt\Traits\Encryption;
use CustomD\EloquentModelEncrypt\KeyProviders\GlobalKeyProvider;
use CustomD\EloquentModelEncrypt\Observers\Encryption as EncryptionObserver;

trait ModelEncryption
{
    use Extenders;
    use Encryption;
    use Decryption;
    use Keystore;

    /**
     * Which Engine are we using to encrypt / decrypt.
     *
     * @var CustomD\EloquentModelEncrypt\Abstracts\Engine
     */
    protected $encryptionEngine;

    /**
     * Header used to identify whether the value is encrypted or not.
     *
     * @var string
     */
    protected static $encryptionHeader = '$cd.enc$';

    protected static $defaultKeyProviders = [
        GlobalKeyProvider::class,
    ];

    protected static function getKeyProviders()
    {
        return self::$keyProviders ?? self::$defaultKeyProviders;
    }

    /**
     * Boot the Encryptable trait for a model.
     */
    public static function bootModelEncryption(): void
    {
        //Initialiase our encryption engine
        //self::initEncryptionEngine();

        //Initialise our observers
        static::observe(EncryptionObserver::class);
    }

    /**
     * Initialialize our encryption engine for this model.
     */
    protected function initEncryptionEngine()
    {
        // Load our config
        $config = config('eloquent-model-encrypt');

        // Encryption Engines available
        $engines = $config['engines'];

        // Table Specific Encryption Engine Listing
        $tables = $config['tables'];

        // Which Engine are we loading
        $engine = isset($tables[self::class]) ? $engines[$tables[self::class]] : $engines['default'];

        // Instansiate our Engine
        $this->encryptionEngine = new $engine();

        return $this->encryptionEngine;
    }

    public function getEncryptionEngine()
    {
        return $this->encryptionEngine ?? $this->initEncryptionEngine();
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function isEncryptable(string $key): bool
    {
        if (! isset($this->encryptable)) {
            return false;
        }

        return in_array($key, $this->encryptable);
    }

    /**
     * checks whether the value is currently encrypted or not.
     *
     * @param string|null $value
     *
     * @return bool
     */
    public function isValueEncrypted(?string $value): bool
    {
        //if position 0 has the header string we are a match :-)
        return strpos($value, self::$encryptionHeader) === 0;
    }
}
