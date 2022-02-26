<?php

namespace YOOtheme\Composer;

use Composer\Script\Event;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ClassmapPlugin
{
    public static function preAutoloadDump(Event $event)
    {
        $io = $event->getIO();
        $composer = $event->getComposer();
        $vendor = $composer->getConfig()->get('vendor-dir');
        $autoload = $composer->getPackage()->getAutoload();
        $installed = $composer->getRepositoryManager()->getLocalRepository();

        $extra = $composer->getPackage()->getExtra();
        $classmap = $extra['classmap']['psr-4'] ?? [];
        $visitors = $extra['classmap']['visitors'] ?? [];

        krsort($classmap);

        $parser = static::createParser();
        $printer = static::createPrinter($classmap, $visitors);

        foreach ($installed->getPackages() as $package) {
            $name = $package->getName();

            if ($mapping = static::mapClasses($classmap, $package)) {
                $installed->removePackage($package);
            }

            foreach ($mapping as $namespace => $path) {
                if (!($toPath = static::getPath($autoload, $namespace))) {
                    continue;
                }

                $io->write("{$name} => {$toPath}");

                $dir = static::joinPath($vendor, $name);
                $src = static::joinPath($vendor, $path);
                $dest = static::joinPath(dirname($vendor), $toPath);

                $include = $extra['classmap']['include'][$name] ?? [];
                $exclude = $extra['classmap']['exclude'][$name] ?? [];
                $replace = $extra['classmap']['replace'][$name] ?? [];

                $fs = new Filesystem();
                $fs->mkdir($dest);

                $files = Finder::create()->in($src);

                foreach ((array) $include as $inc) {
                    $files->path($inc);
                }

                foreach ((array) $exclude as $exc) {
                    $files->notPath($exc);
                }

                foreach ($files as $file) {
                    if ($file->isDir()) {
                        $fs->mkdir(static::joinPath($dest, $file->getRelativePathname()));
                    } else {
                        $code = $file->getContents();

                        foreach ((array) $replace as $callback) {
                            if (is_string($result = call_user_func($callback, $file, $code))) {
                                $code = $result;
                            }
                        }

                        $fs->dumpFile(
                            static::joinPath($dest, $file->getRelativePathname()),
                            $printer($file, ...$parser($code))
                        );
                    }
                }
            }
        }
    }

    protected static function mapClasses(array $classmap, $package)
    {
        $autoload = $package->getAutoload()['psr-4'] ?? [];
        $mapping = [];

        foreach ($classmap as $oldNamespace => $newNamespace) {
            $length = strlen($oldNamespace);

            foreach ($autoload as $namespace => $path) {
                if ($oldNamespace === substr($namespace, 0, $length)) {
                    $mapping[$newNamespace] = static::joinPath($package->getName(), $path);
                }
            }
        }

        return $mapping;
    }

    protected static function createParser()
    {
        $lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine',
                'endLine',
                'startTokenPos',
                'endTokenPos',
            ],
        ]);

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);

        return function ($code) use ($parser, $lexer) {
            return [$parser->parse($code), $lexer->getTokens()];
        };
    }

    protected static function createPrinter(array $classmap, array $visitors)
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new CloningVisitor());
        $traverser->addVisitor(new ParentResolver());
        $traverser->addVisitor(new NameResolver(null, ['replaceNodes' => false]));
        $traverser->addVisitor(new NamespaceRenamer($classmap));

        foreach ($visitors as $visitor) {
            $traverser->addVisitor(new $visitor($traverser));
        }

        return function ($file, $stmts, $tokens) use ($traverser) {
            $traverser->file = $file;

            return (new Standard())->printFormatPreserving(
                $traverser->traverse($stmts),
                $stmts,
                $tokens
            );
        };
    }

    protected static function getPath(array $autoload, string $namespace)
    {
        $path = $autoload['psr-4'][$namespace] ?? null;

        return is_array($path) ? $path[0] : $path;
    }

    protected static function joinPath(...$paths)
    {
        return preg_replace('~[/\\\\]+~', '/', join('/', $paths));
    }
}
