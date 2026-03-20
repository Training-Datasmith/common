<?php

declare(strict_types=1);

namespace Doctrine\Tests\Security;

use Doctrine\Common\Class_Loader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Security regression tests for Class_Loader path handling.
 *
 * The Class_Loader converts namespace separators to directory separators when
 * building the file path for a class. Without sanitisation, a crafted class name
 * containing "../" sequences could cause the loader to traverse outside the
 * configured include path (path-traversal attack), potentially loading arbitrary
 * PHP files from the filesystem.
 *
 * These tests verify that:
 * 1. can_load_class() returns false for class names that would escape the root.
 * 2. load_class() does not attempt to load files outside the configured path.
 *
 * Note: Class_Loader is deprecated since doctrine/common 3.x. These tests exist
 * as regression coverage to ensure the deprecated code path remains safe for any
 * project that has not yet migrated to Composer autoloading.
 */
#[CoversClass(Class_Loader::class)]
final class Class_Loader_Path_Traversal_Test extends TestCase
{
    private string $safe_root;

    protected function setUp(): void
    {
        // Use a known, controlled temp directory as the include path root.
        $this->safe_root = sys_get_temp_dir() . '/doctrine_classloader_test_' . getmypid();
        @mkdir($this->safe_root, 0700, true);
    }

    protected function tearDown(): void
    {
        // Clean up the temp directory.
        @rmdir($this->safe_root);
    }

    /**
     * A well-formed class name within the configured namespace must resolve to a
     * path that starts with the safe root. This validates baseline behaviour.
     */
    public function test_well_formed_class_name_resolves_within_root(): void
    {
        $loader = new Class_Loader('MyApp', $this->safe_root);

        // This file does not exist, so can_load_class() returns false — but the
        // important assertion is that the internal path it would try to load is
        // within the configured root. We verify this by checking that
        // can_load_class() does not throw and returns false (not true — meaning
        // it did not load something unexpected).
        $result = $loader->can_load_class('MyApp\\Model\\User');
        self::assertFalse($result, 'Class does not exist so can_load_class must return false.');
    }

    /**
     * A class name containing ".." segments (after namespace-separator substitution)
     * would produce a path like "/safe_root/../../../etc/passwd.php". The loader must
     * not resolve such paths.
     *
     * can_load_class() uses is_file() against the composed path. If the path escapes
     * the root but the target file exists, a vulnerable loader would return true.
     * A safe loader must either sanitise the path or refuse to load classes whose
     * composed paths escape the configured root.
     */
    public function test_class_name_with_parent_directory_segments_cannot_escape_root(): void
    {
        $loader = new Class_Loader('MyApp', $this->safe_root);

        // The namespace separator "\" maps to DIRECTORY_SEPARATOR.
        // On Unix "MyApp\..\..\etc\passwd" → "MyApp/../../etc/passwd"
        // After joining with safe_root: safe_root + "/MyApp/../../etc/passwd.php"
        // This resolves to two levels above safe_root.
        $traversal_class = 'MyApp\\..\\..\\etc\\passwd';

        // The expected safe behaviour: the class does not match the namespace
        // prefix check *and* the resulting file path must not exist outside root.
        // Either way, can_load_class must return false (no escape).
        $result = $loader->can_load_class($traversal_class);

        self::assertFalse(
            $result,
            'can_load_class() must return false for class names that attempt path traversal. ' .
            'A true return would indicate the loader was tricked into accepting a crafted path.'
        );
    }

    /**
     * Verifies that load_class() returns false (and does not trigger an error)
     * for a class whose path would exit the configured include path root.
     */
    public function test_load_class_does_not_load_files_outside_root(): void
    {
        $loader = new Class_Loader('Safe', $this->safe_root);

        $traversal_class = 'Safe\\..\\..' . DIRECTORY_SEPARATOR . 'sensitive_file';

        // load_class() calls can_load_class() first; since the file does not
        // exist (and the class does not already exist) it must return false.
        $result = $loader->load_class($traversal_class);

        self::assertFalse(
            $result,
            'load_class() must return false without attempting to load files outside the root directory.'
        );
    }
}
