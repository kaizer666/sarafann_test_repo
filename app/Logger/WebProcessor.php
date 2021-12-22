<?php

namespace App\Logger;

    use ArrayAccess;
    use Monolog\Processor\ProcessorInterface;
    use UnexpectedValueException;

    class WebProcessor implements ProcessorInterface
    {
        protected $serverData;

        protected $extraFields = [
            'url' => 'REQUEST_URI',
            'ip' => 'REMOTE_ADDR',
            'http_method' => 'REQUEST_METHOD',
            'server' => 'SERVER_NAME',
            'referrer' => 'HTTP_REFERER',
        ];

        public function __construct($serverData = null, array $extraFields = null)
        {
            if (null === $serverData) {
                $this->serverData = &$_SERVER;
            } elseif (is_array($serverData) || $serverData instanceof ArrayAccess) {
                $this->serverData = $serverData;
            } else {
                throw new UnexpectedValueException('$serverData must be an array or object implementing ArrayAccess.');
            }

            if (null !== $extraFields) {
                if (isset($extraFields[0])) {
                    foreach (array_keys($this->extraFields) as $fieldName) {
                        if (!in_array($fieldName, $extraFields)) {
                            unset($this->extraFields[$fieldName]);
                        }
                    }
                } else {
                    $this->extraFields = $extraFields;
                }
            }
        }

        public function __invoke(array $record): array
        {
            if (!isset($this->serverData['REQUEST_URI'])) {
                return $record;
            }
            $record['extra']['web'] = [];
            $record['extra']['web'] = $this->appendExtraFields($record['extra']['web']);

            return $record;
        }

        private function appendExtraFields(array $extra): array
        {
            foreach ($this->extraFields as $extraName => $serverName) {
                $extra[$extraName] = $this->serverData[$serverName] ?? null;
            }

            if (isset($this->serverData['UNIQUE_ID'])) {
                $extra['unique_id'] = $this->serverData['UNIQUE_ID'];
            }

            return $extra;
        }
    }
