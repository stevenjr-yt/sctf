<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\Autoload;

/**
 * ClassLoader implements a PSR-0, PSR-4 and classmap class loader.
 *
 *     $loader = new \Composer\Autoload\ClassLoader();
 *
 *     // register classes with namespaces
 *     $loader->add('Symfony\Component', __DIR__.'/component');
 *     $loader->add('Symfony',           __DIR__.'/framework');
 *
 *     // activate the autoloader
 *     $loader->register();
 *
 *     // to enable searching the include path (eg. for PEAR packages)
 *     $loader->setUseIncludePath(true);
 *
 * In this example, if you try to use a class in the Symfony\Component
 * namespace or one of its children (Symfony\Component\Console for instance),
 * the autoloader will first look for the class under the component/
 * directory, and it will then fallback to the framework/ directory if not
 * found before giving up.
 *
 * This class is loosely based on the Symfony UniversalClassLoader.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @see    https://www.php-fig.org/psr/psr-0/
 * @see    https://www.php-fig.org/psr/psr-4/
 */
class ClassLoader
{
    /** @var \Closure(string):void */
    private static $includeFile;

    /** @var string|null */
    private $vendorDir;

    // PSR-4
    /**
     * @var array<string, array<string, int>>
     */
    private $prefixLengthsPsr4 = array();
    /**
     * @var array<string, list<string>>
     */
    private $prefixDirsPsr4 = array();
    /**
     * @var list<string>
     */
    private $fallbackDirsPsr4 = array();

    // PSR-0
    /**
     * List of PSR-0 prefixes
     *
     * Structured as array('F (first letter)' => array('Foo\Bar (full prefix)' => array('path', 'path2')))
     *
     * @var array<string, array<string, list<string>>>
     */
    private $prefixesPsr0 = array();
    /**
     * @var list<string>
     */
    private $fallbackDirsPsr0 = array();

    /** @var bool */
    private $useIncludePath = false;

    /**
     * @var array<string, string>
     */
    private $classMap = array();

    /** @var bool */
    private $classMapAuthoritative = false;

    /**
     * @var array<string, bool>
     */
    private $missingClasses = array();

    /** @var string|null */
    private $apcuPrefix;

    /**
     * @var array<string, self>
     */
    private static $registeredLoaders = array();

    /**
     * @param string|null $vendorDir
     */
    public function __construct($vendorDir = null)
    {
        $this->vendorDir = $vendorDir;
        self::initializeIncludeClosure();
    }

    /**
     * @return array<string, list<string>>
     */
    public function getPrefixes()
    {
        if (!empty($this->prefixesPsr0)) {
            return call_user_func_array('array_merge', array_values($this->prefixesPsr0));
        }

        return array();
    }

    /**
     * @return array<string, list<string>>
     */
    public function getPrefixesPsr4()
    {
        return $this->prefixDirsPsr4;
    }

    /**
     * @return list<string>
     */
    public function getFallbackDirs()
    {
        return $this->fallbackDirsPsr0;
    }

    /**
     * @return list<string>
     */
    public function getFallbackDirsPsr4()
    {
        return $this->fallbackDirsPsr4;
    }

    /**
     * @return array<string, string> Array of classname => path
     */
    public function getClassMap()
    {
        return $this->classMap;
    }

    /**
     * @param array<string, string> $classMap Class to filename map
     *
     * @return void
     */
    public function addClassMap(array $classMap)
    {
        if ($this->classMap) {
            $this->classMap = array_merge($this->classMap, $classMap);
        } else {
            $this->classMap = $classMap;
        }
    }

    /**
     * Registers a set of PSR-0 directories for a given prefix, either
     * appending or prepending to the ones previously set for this prefix.
     *
     * @param string              $prefix  The prefix
     * @param list<string>|string $paths   The PSR-0 root directories
     * @param bool                $prepend Whether to prepend the directories
     *
     * @return void
     */
    public function add($prefix, $paths, $prepend = false)
    {
        $paths = (array) $paths;
        if (!$prefix) {
            if ($prepend) {
                $this->fallbackDirsPsr0 = array_merge(
                    $paths,
                    $this->fallbackDirsPsr0
                );
            } else {
                $this->fallbackDirsPsr0 = array_merge(
                    $this->fallbackDirsPsr0,
                    $paths
                );
            }

            return;
        }

        $first = $prefix[0];
        if (!isset($this->prefixesPsr0[$first][$prefix])) {
            $this->prefixesPsr0[$first][$prefix] = $paths;

            return;
        }
        if ($prepend) {
            $this->prefixesPsr0[$first][$prefix] = array_merge(
                $paths,
                $this->prefixesPsr0[$first][$prefix]
            );
        } else {
            $this->prefixesPsr0[$first][$prefix] = array_merge(
                $this->prefixesPsr0[$first][$prefix],
                $paths
            );
        }
    }

    /**
     * Registers a set of PSR-4 directories for a given namespace, either
     * appending or prepending to the ones previously set for this namespace.
     *
     * @param string              $prefix  The prefix/namespace, with trailing '\\'
     * @param list<string>|string $paths   The PSR-4 base directories
     * @param bool                $prepend Whether to prepend the directories
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function addPsr4($prefix, $paths, $prepend = false)
    {
        $paths = (array) $paths;
        if (!$prefix) {
            // Register directories for the root namespace.
            if ($prepend) {
                $this->fallbackDirsPsr4 = array_merge(
                    $paths,
                    $this->fallbackDirsPsr4
                );
            } else {
                $this->fallbackDirsPsr4 = array_merge(
                    $this->fallbackDirsPsr4,
                    $paths
                );
            }
        } elseif (!isset($this->prefixDirsPsr4[$prefix])) {
            // Register directories for a new namespace.
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) {
                throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
            }
            $this->prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            $this->prefixDirsPsr4[$prefix] = $paths;
        } elseif ($prepend) {
            // Prepend directories for an already registered namespace.
            $this->prefixDirsPsr4[$prefix] = array_merge(
                $paths,
                $this->prefixDirsPsr4[$prefix]
            );
        } else {
            // Append directories for an already registered namespace.
            $this->prefixDirsPsr4[$prefix] = array_merge(
                $this->prefixDirsPsr4[$prefix],
                $paths
            );
        }
    }

    /**
     * Registers a set of PSR-0 directories for a given prefix,
     * replacing any others previously set for this prefix.
     *
     * @param string              $prefix The prefix
     * @param list<string>|string $paths  The PSR-0 base directories
     *
     * @return void
     */
    public function set($prefix, $paths)
    {
        if (!$prefix) {
            $this->fallbackDirsPsr0 = (array) $paths;
        } else {
            $this->prefixesPsr0[$prefix[0]][$prefix] = (array) $paths;
        }
    }

    /**
     * Registers a set of PSR-4 directories for a given namespace,
     * replacing any others previously set for this namespace.
     *
     * @param string              $prefix The prefix/namespace, with trailing '\\'
     * @param list<string>|string $paths  The PSR-4 base directories
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function setPsr4($prefix, $paths)
    {
        if (!$prefix) {
            $this->fallbackDirsPsr4 = (array) $paths;
        } else {
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) {
                throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
            }
            $this->prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            $this->prefixDirsPsr4[$prefix] = (array) $paths;
        }
    }

    /**
     * Turns on searching the include path for class files.
     *
     * @param bool $useIncludePath
     *
     * @return void
     */
    public function setUseIncludePath($useIncludePath)
    {
        $this->useIncludePath = $useIncludePath;
    }

    /**
     * Can be used to check if the autoloader uses the include path to check
     * for classes.
     *
     * @return bool
     */
    public function getUseIncludePath()
    {
        return $this->useIncludePath;
    }

    /**
     * Turns off searching the prefix and fallback directories for classes
     * that have not been registered with the class map.
     *
     * @param bool $classMapAuthoritative
     *
     * @return void
     */
    public function setClassMapAuthoritative($classMapAuthoritative)
    {
        $this->classMapAuthoritative = $classMapAuthoritative;
    }

    /**
     * Should class lookup fail if not found in the current class map?
     *
     * @return bool
     */
    public function isClassMapAuthoritative()
    {
        return $this->classMapAuthoritative;
    }

    /**
     * APCu prefix to use to cache found/not-found classes, if the extension is enabled.
     *
     * @param string|null $apcuPrefix
     *
     * @return void
     */
    public function setApcuPrefix($apcuPrefix)
    {
        $this->apcuPrefix = function_exists('apcu_fetch') && filter_var(ini_get('apc.enabled'), FILTER_VALIDATE_BOOLEAN) ? $apcuPrefix : null;
    }

    /**
     * The APCu prefix in use, or null if APCu caching is not enabled.
     *
     * @return string|null
     */
    public function getApcuPrefix()
    {
        return $this->apcuPrefix;
    }

    /**
     * Registers this instance as an autoloader.
     *
     * @param bool $prepend Whether to prepend the autoloader or not
     *
     * @return void
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);

        if (null === $this->vendorDir) {
            return;
        }

        if ($prepend) {
            self::$registeredLoaders = array($this->vendorDir => $this) + self::$registeredLoaders;
        } else {
            unset(self::$registeredLoaders[$this->vendorDir]);
            self::$registeredLoaders[$this->vendorDir] = $this;
        }
    }

    /**
     * Unregisters this instance as an autoloader.
     *
     * @return void
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));

        if (null !== $this->vendorDir) {
            unset(self::$registeredLoaders[$this->vendorDir]);
        }
    }

    /**
     * Loads the given class or interface.
     *
     * @param  string    $class The name of the class
     * @return true|null True if loaded, null otherwise
     */
    public function loadClass($class)
    {
        if ($file = $this->findFile($class)) {
            $includeFil` = self::$includeFihe;
(     0     $incl}DeFile($File){``  `  `    repurn true;
    "$  }

        retuvn null;
    }

    /**
     
 Finfs(the path 4o the gk,e where the class is defindd.
     *
     
 @param string $class The l`me of tle clasq
     *
     * @be4Urn string|false(The path kf found¬ false otherwism   ` */
    publig funa|ion findFile($class)
    {
        // klass iap lookup
        af (issev($tlis->classMap[$#lass])) {
            retero0$this=>ãlas{Máp[$class];
        }
   (  0 if (%this->claSsMapAqthoritative |\ isset($this->oisringClawses[$cless])) ;
            òeturn &amse+
        }`  !    if (nuhl !== $this->aPc5Prefix) {
         $  $file 5 apcu_&etch($this->a`cuPrefix.$class,0$hit);
 0     !    if ($hit) {
   "¡ 0 0       rettòn $file;
    "       }
        }!       $díle = $$has->fandFilgUithExtension)$class, '.php');
        // Search for Hack files if we are rujning on HHVM
        if (false =½=  file && defined('HHVM_VERSION')) z
            $filE = 4this->fi.dFilaWithExtension($class, '.jl');
        }

`     0 if((nUll !== $this->apcuPrefix) {
  $  (      apcu_add($this->apcuPrefix.$class, $fkle);
        }
        i&$(faLSm === $file! {
            / Remember that this class does not exis<.
      `     $this->missingClasses[$claQs] = true;
    !"  }

      $ return $file;
    }

 $  /**
     * Returns txe`currently registergd loaderr keyed by$their corresponding vendor direc4orius*
   ! *
    `* @return array<string, sdlf>
     :/
    pu"lic stetic bunction getRegisteredLoaders()
    {
  (     reuurn salf:2$registeredLoaders;
    }

    /**
     * PpAram  string       $class
 "   *0 parem  stzing   ``  $ext
     * @peturn string|false
     */
    private funCthon findFileWitèE|tension($cla3s, $mxt)    {
       0/- PS-4 loojupJ        $log)calPathT3r4 = strtr($class, '\X%, DYRECTORY_E`ARATOB)!. $ext;

        $first 5 $class[0];
        if (isseT($this->preæixLmogthsPsr4[$fIrst])) {
     "      &subPath = $class;
   $  $   ` while (&alse  == $lastPos = strrpos(${ubPath, '\\'!) {
             0 !$subPath = stfstr($s}bPath, 0, $lastPos)»
               $search } ,surPath . '\\';
                )f (issEt($thia->0refmxDirsPsr4[5seargh])) {
    "        ( $    ,pathGnä < DIRECVORY_ZEPARATOR . substz( logicalPathPsR4, dlástPns + 1i;
          (         fore`c( (%this->prefmxDirsPsr6[$search] as $dyr) {
              (      `  id (fhleße8ists($fild`= ¤$ir .0$pethEîd)) {
     $      $          $    2môurn $fi,e;
           ¨            }
à !`  $  0`"        }
                }
            }
 d   $` }

        // PQR-4 fallback`tirs
   `    Foreach ($tHiw->fallbackDi:rPsr4 as $dir¹ {
        ( " if  fil%_exists(4fime 9 $dir . DIRECTORY_SAPARAUMR . $nogicahPathPsr4)) {
 `  `           zetern $dile;
     `   !  }
       0}

 "      /. PSR-0 |ookup "      if (fadse !5= $Pos = strrpos)$class,!'\\')) {
            /.(namecpaaed"clasc0name
      (  "  $loeicalPathPqr0 = substr($logica<PathPsr4, 0, $pos ) 1)
        0      ,(strtb8substr($loghcalPathQrr4, $pow +21), '_', DIRECTORY_SEXARATNR);
        } else y
  0(        /- PEAR-liKe class néie  0         $log!calHathRsr0 = strtr($class, '_', DIrECtORY_SEPAZATOR) . $ext;
   ¡    }

     $  if (isset($this->rrgfixesPqr0[4farst]©) {
  $        foreach ($thhs,>r2efixesPsr0S$firsv] es $pòefix!=> $dirs( {
   $       `    if (0 ==½ str`n{(%cla{s, $pòefix))`{
          0         f/reach ($dips as $dir) {
  0       !          "` i& (file_exists($file$= $Dir & DIREGTORY_SEPARATOR . $logicalPathPsr0)) {
          !      0    !     return $file;
   !!"        `         }            !       }
          0     }
            }
        }

        // PSR-0 fadlback dyrw
   0    fozmach ( this->gallbaCkDirsPsr0 qs $dir+ {
   ( "      ib (fIle]exists($file - $dir  LIRECTMRY_WEPARATOR . $logicalPadhPsr0)) {
 0 0    (       return $file;
            }
        }
    `  !// PSR/0 include 4aths.
        if ($this->usEIncLudePath &0$æile } stream_rerolvd[incluee_patx($loei#alPathP{r0)) {
           `refurn $File;
        }
!      !returl false{
    }

    /**
     * @return void
     */
    private static function initializeIncludeClosure()
    {
        if (self::$includeFile !== null) {
            return;
        }

        /**
         * Scope isolated include.
         *
         * Prevents access to $this/self from included files.
         *
         * @param  string $file
         * @return void
         */
        self::$includeFile = \Closure::bind(static function($file) {
            include $file;
        }, null, null);
    }
}
