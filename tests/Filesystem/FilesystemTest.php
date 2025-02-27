<?php

use Illuminate\Filesystem\Filesystem;

class FilesystemTest extends PHPUnit_Framework_TestCase
{
    private $tempDir;

    public function setUp()
    {
        $this->tempDir = __DIR__.'/tmp';
        mkdir($this->tempDir);
    }

    public function tearDown()
    {
        $files = new Filesystem();
        $files->deleteDirectory($this->tempDir);
    }

    public function testGetRetrievesFiles()
    {
        file_put_contents($this->tempDir.'/file.txt', 'Hello World');
        $files = new Filesystem();
        $this->assertEquals('Hello World', $files->get($this->tempDir.'/file.txt'));
    }

    public function testPutStoresFiles()
    {
        $files = new Filesystem();
        $files->put($this->tempDir.'/file.txt', 'Hello World');
        $this->assertStringEqualsFile($this->tempDir.'/file.txt', 'Hello World');
    }

    public function testDeleteRemovesFiles()
    {
        file_put_contents($this->tempDir.'/file.txt', 'Hello World');
        $files = new Filesystem();
        $files->delete($this->tempDir.'/file.txt');
        $this->assertFileNotExists($this->tempDir.'/file.txt');
    }

    public function testPrependExistingFiles()
    {
        $files = new Filesystem();
        $files->put($this->tempDir.'/file.txt', 'World');
        $files->prepend($this->tempDir.'/file.txt', 'Hello ');
        $this->assertStringEqualsFile($this->tempDir.'/file.txt', 'Hello World');
    }

    public function testPrependNewFiles()
    {
        $files = new Filesystem();
        $files->prepend($this->tempDir.'/file.txt', 'Hello World');
        $this->assertStringEqualsFile($this->tempDir.'/file.txt', 'Hello World');
    }

    public function testDeleteDirectory()
    {
        mkdir($this->tempDir.'/foo');
        file_put_contents($this->tempDir.'/foo/file.txt', 'Hello World');
        $files = new Filesystem();
        $files->deleteDirectory($this->tempDir.'/foo');
        $this->assertFalse(is_dir($this->tempDir.'/foo'));
        $this->assertFileNotExists($this->tempDir.'/foo/file.txt');
    }

    public function testCleanDirectory()
    {
        mkdir($this->tempDir.'/foo');
        file_put_contents($this->tempDir.'/foo/file.txt', 'Hello World');
        $files = new Filesystem();
        $files->cleanDirectory($this->tempDir.'/foo');
        $this->assertTrue(is_dir($this->tempDir.'/foo'));
        $this->assertFileNotExists($this->tempDir.'/foo/file.txt');
    }

    public function testMacro()
    {
        file_put_contents($this->tempDir.'/foo.txt', 'Hello World');
        $files = new Filesystem();
        $tempDir = $this->tempDir;
        $files->macro('getFoo', function () use ($files, $tempDir) {
            return $files->get($tempDir.'/foo.txt');
        });
        $this->assertEquals('Hello World', $files->getFoo());
    }

    public function testFilesMethod()
    {
        mkdir($this->tempDir.'/foo');
        file_put_contents($this->tempDir.'/foo/1.txt', '1');
        file_put_contents($this->tempDir.'/foo/2.txt', '2');
        mkdir($this->tempDir.'/foo/bar');
        $files = new Filesystem();
        $this->assertEquals([$this->tempDir.'/foo/1.txt', $this->tempDir.'/foo/2.txt'], $files->files($this->tempDir.'/foo'));
        unset($files);
    }

    public function testCopyDirectoryReturnsFalseIfSourceIsntDirectory()
    {
        $files = new Filesystem();
        $this->assertFalse($files->copyDirectory($this->tempDir.'/foo/bar/baz/breeze/boom', $this->tempDir));
    }

    public function testCopyDirectoryMovesEntireDirectory()
    {
        mkdir($this->tempDir.'/tmp', 0777, true);
        file_put_contents($this->tempDir.'/tmp/foo.txt', '');
        file_put_contents($this->tempDir.'/tmp/bar.txt', '');
        mkdir($this->tempDir.'/tmp/nested', 0777, true);
        file_put_contents($this->tempDir.'/tmp/nested/baz.txt', '');

        $files = new Filesystem();
        $files->copyDirectory($this->tempDir.'/tmp', $this->tempDir.'/tmp2');
        $this->assertTrue(is_dir($this->tempDir.'/tmp2'));
        $this->assertFileExists($this->tempDir.'/tmp2/foo.txt');
        $this->assertFileExists($this->tempDir.'/tmp2/bar.txt');
        $this->assertTrue(is_dir($this->tempDir.'/tmp2/nested'));
        $this->assertFileExists($this->tempDir.'/tmp2/nested/baz.txt');
    }

    public function testMoveDirectoryMovesEntireDirectory()
    {
        mkdir($this->tempDir.'/tmp', 0777, true);
        file_put_contents($this->tempDir.'/tmp/foo.txt', '');
        file_put_contents($this->tempDir.'/tmp/bar.txt', '');
        mkdir($this->tempDir.'/tmp/nested', 0777, true);
        file_put_contents($this->tempDir.'/tmp/nested/baz.txt', '');

        $files = new Filesystem();
        $files->moveDirectory($this->tempDir.'/tmp', $this->tempDir.'/tmp2');
        $this->assertTrue(is_dir($this->tempDir.'/tmp2'));
        $this->assertFileExists($this->tempDir.'/tmp2/foo.txt');
        $this->assertFileExists($this->tempDir.'/tmp2/bar.txt');
        $this->assertTrue(is_dir($this->tempDir.'/tmp2/nested'));
        $this->assertFileExists($this->tempDir.'/tmp2/nested/baz.txt');
        $this->assertFalse(is_dir($this->tempDir.'/tmp'));
    }

    public function testMoveDirectoryMovesEntireDirectoryAndOverwrites()
    {
        mkdir($this->tempDir.'/tmp', 0777, true);
        file_put_contents($this->tempDir.'/tmp/foo.txt', '');
        file_put_contents($this->tempDir.'/tmp/bar.txt', '');
        mkdir($this->tempDir.'/tmp/nested', 0777, true);
        file_put_contents($this->tempDir.'/tmp/nested/baz.txt', '');
        mkdir($this->tempDir.'/tmp2', 0777, true);
        file_put_contents($this->tempDir.'/tmp2/foo2.txt', '');
        file_put_contents($this->tempDir.'/tmp2/bar2.txt', '');

        $files = new Filesystem();
        $files->moveDirectory($this->tempDir.'/tmp', $this->tempDir.'/tmp2', true);
        $this->assertTrue(is_dir($this->tempDir.'/tmp2'));
        $this->assertFileExists($this->tempDir.'/tmp2/foo.txt');
        $this->assertFileExists($this->tempDir.'/tmp2/bar.txt');
        $this->assertTrue(is_dir($this->tempDir.'/tmp2/nested'));
        $this->assertFileExists($this->tempDir.'/tmp2/nested/baz.txt');
        $this->assertFileNotExists($this->tempDir.'/tmp2/foo2.txt');
        $this->assertFileNotExists($this->tempDir.'/tmp2/bar2.txt');
        $this->assertFalse(is_dir($this->tempDir.'/tmp'));
    }

    /**
     * @expectedException Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function testGetThrowsExceptionNonexisitingFile()
    {
        $files = new Filesystem();
        $files->get($this->tempDir.'/unknown-file.txt');
    }

    public function testGetRequireReturnsProperly()
    {
        file_put_contents($this->tempDir.'/file.php', '<?php return "Howdy?"; ?>');
        $files = new Filesystem();
        $this->assertEquals('Howdy?', $files->getRequire($this->tempDir.'/file.php'));
    }

    /**
     * @expectedException Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function testGetRequireThrowsExceptionNonexisitingFile()
    {
        $files = new Filesystem();
        $files->getRequire($this->tempDir.'/file.php');
    }

    public function testAppendAddsDataToFile()
    {
        file_put_contents($this->tempDir.'/file.txt', 'foo');
        $files = new Filesystem();
        $bytesWritten = $files->append($this->tempDir.'/file.txt', 'bar');
        $this->assertEquals(mb_strlen('bar', '8bit'), $bytesWritten);
        $this->assertFileExists($this->tempDir.'/file.txt');
        $this->assertStringEqualsFile($this->tempDir.'/file.txt', 'foobar');
    }

    public function testMoveMovesFiles()
    {
        file_put_contents($this->tempDir.'/foo.txt', 'foo');
        $files = new Filesystem();
        $files->move($this->tempDir.'/foo.txt', $this->tempDir.'/bar.txt');
        $this->assertFileExists($this->tempDir.'/bar.txt');
        $this->assertFileNotExists($this->tempDir.'/foo.txt');
    }

    public function testExtensionReturnsExtension()
    {
        file_put_contents($this->tempDir.'/foo.txt', 'foo');
        $files = new Filesystem();
        $this->assertEquals('txt', $files->extension($this->tempDir.'/foo.txt'));
    }

    public function testBasenameReturnsBasename()
    {
        file_put_contents($this->tempDir.'/foo.txt', 'foo');
        $files = new Filesystem();
        $this->assertEquals('foo.txt', $files->basename($this->tempDir.'/foo.txt'));
    }

    public function testDirnameReturnsDirectory()
    {
        file_put_contents($this->tempDir.'/foo.txt', 'foo');
        $files = new Filesystem();
        $this->assertEquals($this->tempDir, $files->dirname($this->tempDir.'/foo.txt'));
    }

    public function testTypeIndentifiesFile()
    {
        file_put_contents($this->tempDir.'/foo.txt', 'foo');
        $files = new Filesystem();
        $this->assertEquals('file', $files->type($this->tempDir.'/foo.txt'));
    }

    public function testTypeIndentifiesDirectory()
    {
        mkdir($this->tempDir.'/foo');
        $files = new Filesystem();
        $this->assertEquals('dir', $files->type($this->tempDir.'/foo'));
    }

    public function testSizeOutputsSize()
    {
        $size = file_put_contents($this->tempDir.'/foo.txt', 'foo');
        $files = new Filesystem();
        $this->assertEquals($size, $files->size($this->tempDir.'/foo.txt'));
    }

    /**
     * @requires extension fileinfo
     */
    public function testMimeTypeOutputsMimeType()
    {
        file_put_contents($this->tempDir.'/foo.txt', 'foo');
        $files = new Filesystem();
        $this->assertEquals('text/plain', $files->mimeType($this->tempDir.'/foo.txt'));
    }

    public function testIsWritable()
    {
        file_put_contents($this->tempDir.'/foo.txt', 'foo');
        $files = new Filesystem();
        @chmod($this->tempDir.'/foo.txt', 0444);
        $this->assertFalse($files->isWritable($this->tempDir.'/foo.txt'));
        @chmod($this->tempDir.'/foo.txt', 0777);
        $this->assertTrue($files->isWritable($this->tempDir.'/foo.txt'));
    }

    public function testGlobFindsFiles()
    {
        file_put_contents($this->tempDir.'/foo.txt', 'foo');
        file_put_contents($this->tempDir.'/bar.txt', 'bar');
        $files = new Filesystem();
        $glob = $files->glob($this->tempDir.'/*.txt');
        $this->assertContains($this->tempDir.'/foo.txt', $glob);
        $this->assertContains($this->tempDir.'/bar.txt', $glob);
    }

    public function testAllFilesFindsFiles()
    {
        file_put_contents($this->tempDir.'/foo.txt', 'foo');
        file_put_contents($this->tempDir.'/bar.txt', 'bar');
        $files = new Filesystem();
        $allFiles = [];
        foreach ($files->allFiles($this->tempDir) as $file) {
            $allFiles[] = $file->getFilename();
        }
        $this->assertContains('foo.txt', $allFiles);
        $this->assertContains('bar.txt', $allFiles);
    }

    public function testDirectoriesFindsDirectories()
    {
        mkdir($this->tempDir.'/foo');
        mkdir($this->tempDir.'/bar');
        $files = new Filesystem();
        $directories = $files->directories($this->tempDir);
        $this->assertContains($this->tempDir.DIRECTORY_SEPARATOR.'foo', $directories);
        $this->assertContains($this->tempDir.DIRECTORY_SEPARATOR.'bar', $directories);
    }

    public function testMakeDirectory()
    {
        $files = new Filesystem();
        $this->assertTrue($files->makeDirectory($this->tempDir.'/foo'));
        $this->assertFileExists($this->tempDir.'/foo');
    }

    /**
     * @requires extension pcntl
     */
    public function testSharedGet()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Skip HHVM test due to bug: https://github.com/facebook/hhvm/issues/5657');
        }

        if (! function_exists('pcntl_fork')) {
            $this->markTestSkipped('Skipping since the pcntl extension is not available');
        }

        $content = '';
        for ($i = 0; $i < 1000000; $i++) {
            $content .= $i;
        }
        $result = 1;

        for ($i = 1; $i <= 20; $i++) {
            $pid = pcntl_fork();

            if (! $pid) {
                $files = new Filesystem();
                $files->put($this->tempDir.'/file.txt', $content, true);
                $read = $files->get($this->tempDir.'/file.txt', true);

                exit(($read === $content) ? 1 : 0);
            }
        }

        while (pcntl_waitpid(0, $status) != -1) {
            $status = pcntl_wexitstatus($status);
            $result *= $status;
        }

        $this->assertTrue($result === 1);
    }

    public function testRequireOnceRequiresFileProperly()
    {
        $filesystem = new Filesystem();
        mkdir($this->tempDir.'/foo');
        file_put_contents($this->tempDir.'/foo/foo.php', '<?php function random_function_xyz(){};');
        $filesystem->requireOnce($this->tempDir.'/foo/foo.php');
        file_put_contents($this->tempDir.'/foo/foo.php', '<?php function random_function_xyz_changed(){};');
        $filesystem->requireOnce($this->tempDir.'/foo/foo.php');
        $this->assertTrue(function_exists('random_function_xyz'));
        $this->assertFalse(function_exists('random_function_xyz_changed'));
    }

    public function testCopyCopiesFileProperly()
    {
        $filesystem = new Filesystem();
        $data = 'contents';
        mkdir($this->tempDir.'/foo');
        file_put_contents($this->tempDir.'/foo/foo.txt', $data);
        $filesystem->copy($this->tempDir.'/foo/foo.txt', $this->tempDir.'/foo/foo2.txt');
        $this->assertTrue(file_exists($this->tempDir.'/foo/foo2.txt'));
        $this->assertEquals($data, file_get_contents($this->tempDir.'/foo/foo2.txt'));
    }

    public function testIsFileChecksFilesProperly()
    {
        $filesystem = new Filesystem();
        mkdir($this->tempDir.'/foo');
        file_put_contents($this->tempDir.'/foo/foo.txt', 'contents');
        $this->assertTrue($filesystem->isFile($this->tempDir.'/foo/foo.txt'));
        $this->assertFalse($filesystem->isFile($this->tempDir.'./foo'));
    }
}
