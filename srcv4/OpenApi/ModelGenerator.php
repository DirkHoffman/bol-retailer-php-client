<?php


namespace Picqer\BolRetailerV4\OpenApi;

class ModelGenerator extends ModelCreator
{
    public function generateModels(): void
    {
        foreach ($this->specs['definitions'] as $type => $modelDefinition) {
            // ignore definitions like 'Container for the order items that have to be cancelled.'
            if (strpos($type, ' ') !== false) {
                continue;
            }

            $this->generateModel($type);
        }
    }

    public function generateModel($type): void
    {
        echo $type . "\n";

        $modelDefinition = $this->specs['definitions'][$type];

        $code = [];
        $code[] = '<?php';
        $code[] = '';
        $code[] = sprintf('namespace %s;', $this->getModelNamespace());
        $code[] = '';
        $code[] = '// This class is auto generated by OpenApi\ModelGenerator';
        $code[] = sprintf('class %s extends AbstractModel', $type);
        $code[] = '{';
        // TODO Add enums
        $this->generateDefinition($modelDefinition, $code);
        $this->generateFields($modelDefinition, $code);
        $this->generateDateTimeGetters($modelDefinition, $code);
        $code[] = '}';
        $code[] = '';

        //print_r($modelDefinition);

        file_put_contents(__DIR__ . '/../Model/' . $type . '.php', implode("\n", $code));
    }

    protected function generateDefinition(array $modelDefinition, array &$code): void
    {
        $code[] = '    protected static $modelDefinition = [';



        foreach ($modelDefinition['properties'] as $name => $propDefinition) {
            $model = 'null';
            $array = 'false';

            if (isset($propDefinition['type'])) {
                if ($propDefinition['type'] == 'array') {
                    $array = 'true';
                    if (isset($propDefinition['items']['$ref'])) {
                        $model = $this->getType($propDefinition['items']['$ref']) . '::class';
                    }
                }
            } elseif (isset($propDefinition['$ref'])) {
                $model = $this->getType($propDefinition['$ref']) . '::class';
            } else {
                // TODO create exception class for this one
                throw new \Exception('Unknown property definition');
            }

            $code[] = sprintf('        \'%s\' => [ \'model\' => %s, \'array\' => %s ],', $name, $model, $array);
        }

        $code[] = '    ];';
    }

    protected function generateFields(array $modelDefinition, array &$code): void
    {
        $propTypeMapping = [
            'array' => 'array',
            'string' => 'string',
            'boolean' => 'bool',
            'integer' => 'int',
            'float' => 'float',
            'number' => 'float'
        ];

        foreach ($modelDefinition['properties'] as $name => $propDefinition) {
            $isObjectArray = false;

            if (isset($propDefinition['type'])) {
                $propType = $propTypeMapping[$propDefinition['type']];
                if ($propType == 'array' && isset($propDefinition['items']['$ref'])) {
                    $propType = $this->getType($propDefinition['items']['$ref']) . '[]';
                    $isObjectArray = true;
                }
            } elseif (isset($propDefinition['$ref'])) {
                $propType = $this->getType($propDefinition['$ref']);
            } else {
                // TODO create exception class for this one
                throw new \Exception('Unknown property definition');
            }

            $code[] = '';
            $code[] = '    /**';

            if ($isObjectArray) {
                $code[] = sprintf('     * @var %s', $propType);
            } else {
                $code[] = sprintf('     * @var %s %s', $propType, $propDefinition['description']);
            }

            $code[] = '     */';
            $code[] = sprintf('    public $%s;', $name);
        }
    }

    protected function generateDateTimeGetters(array $modelDefinition, array &$code): void
    {
        foreach ($modelDefinition['properties'] as $name => $propDefinition) {
            if (strpos($name, 'DateTime') === false) {
                continue;
            }

            $code[] = '';
            $code[] = sprintf('    public function get%s(): ?\DateTime', ucfirst($name));
            $code[] = '    {';
            $code[] = sprintf('        if (empty($this->%s)) {', $name);
            $code[] = '            return null;';
            $code[] = '        }';
            $code[] = '';
            $code[] = sprintf('        return \DateTime::createFromFormat(\DateTime::ATOM, $this->%s);', $name);
            $code[] = '    }';
        }
    }
}
