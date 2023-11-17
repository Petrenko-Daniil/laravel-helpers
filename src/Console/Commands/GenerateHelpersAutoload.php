<?php

namespace DanilPetrenko\LaravelHelpers\Console\Commands;

use Illuminate\Support\Facades\Config;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class GenerateHelpersAutoload extends \Illuminate\Console\Command
{
    protected $signature = 'generate-helpers-autoload';

    protected $description = 'Generating helpers autoload file for composer autoload';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->line('Generating...');
        $declaredFunctionNames = []; //array to prevent the same function names
        /**
         * Create autoload file
         */
        $file = fopen('bootstrap/helpers.php', 'w+');
        fwrite($file, '<?php '.PHP_EOL);
        /**
         * Get list of existing classes inside specified folder
         */
        if (!file_exists('app/Helpers')){
            mkdir('app/Helpers', 0777, true);
        }
        $files = scandir('app/Helpers');
        $existingClasses = [];
        foreach ($files as $filename){
            $className = str_replace('.php', '', $filename);
            if (class_exists("App\\Helpers\\".$className))
                $existingClasses[] = "App\\Helpers\\".$className;
        }

        /**
         * Reflection of given classes and then parsing it`s static methods
         * to functions with the same name which can be used to shortly call
         * static method without specifying class
         */
        $methodsMap = [];
        foreach ($existingClasses as $class){
            try {
                $reflection = new ReflectionClass($class); //reflection of the class
                $methods = $reflection->getMethods(ReflectionMethod::IS_STATIC and ReflectionMethod::IS_PUBLIC);//get methods list
                foreach ($methods as $method){
                    //excluding magic methods and methods with the same name
                    if (str_contains($method->getName(), '__') or in_array($method->getName(), $declaredFunctionNames))
                        continue;
                    //preparing function declaration
                    $parametersList = '';
                    $functionDeclaration = ''
                        .$method->getDocComment().PHP_EOL
                        .'function '.$method->name.'(';
                    foreach ($method->getParameters() as $key=>$parameter){
                        /**
                         *  Preparing valid function parameters to be able to see
                         *  list of required parameters and default values in IDEs
                         *  can be simply replaced with ...$vars
                         */
                        if ($key != 0){
                            $functionDeclaration .= ', ';
                            $parametersList .= ', ';
                        }
                        $parametersList .= '$'.$parameter->getName();
                        if($parameter->getType()){
                            $functionDeclaration .= $parameter->getType()->getName().' ';
                        }
                        $functionDeclaration .= '$'.$parameter->getName();
                        if ($parameter->isDefaultValueAvailable()){
                            $functionDeclaration .= ' = '.($parameter->getDefaultValue()
                                    ? (is_string($parameter->getDefaultValue())
                                        ? '"'.$parameter->getDefaultValue().'"'
                                        : (is_array($parameter->getDefaultValue())
                                            ? json_encode($parameter->getDefaultValue())
                                            : $parameter->getDefaultValue()
                                        ))
                                    : 'null');
                        }
                    }
                    $functionDeclaration .= ')';
                    /**
                     * Specifying function return type, will be set void even if static method
                     * has return, but doesn't specify it in function declaration. Specifying
                     * return type is required, otherwise void will be set.
                     */
                    if ($method->getReturnType()){
                        $functionDeclaration .= ': '.$method->getReturnType()->getName();
                    } else {
                        $functionDeclaration .= ': void';
                    }
                    $hasReturn = ($method->getReturnType() && !($method->getReturnType()->getName() == 'void'));
                    /**
                     * Static method is called
                     */
                    $functionDeclaration .= '{'.PHP_EOL
                        .'  '.($hasReturn ? 'return ' : '').$class.'::'.$method->getName().'('.$parametersList.');'.PHP_EOL
                        .'}'.PHP_EOL;
                    fwrite($file, $functionDeclaration);
                    $declaredFunctionNames[] = $method->getName();
                }
            } catch (ReflectionException $e) {
                $this->warn($class.': ReflectionException was caught. This class won`t be processed');
            }
        }
        fclose($file);
        $this->info('Available helpers: ');
        \Symfony\Component\VarDumper\VarDumper::dump($declaredFunctionNames);
        $this->info('Add /bootstrap/helpers.php to composer autoload');
    }
}