<?php declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Support\Str;

use UnexpectedValueException;

test('with end',
    function($paths, $ex) {
        expect(Pathering::withEnd(...(array)$paths))->toBe($ex);
    })->with([
                 ['test/testings', 'test/testings/'],
                 ['test/testings/', 'test/testings/'],
                 [['test', 'testings'], 'test/testings/'],
                 [['test', 'testings/'], 'test/testings/'],
             ]);

test('keep slashes',
    function($paths, $ex) {
        expect(Pathering::keepSlashes(...(array)$paths))->toBe($ex);
    })->with([
                 ['test/testings', 'test/testings'],
                 ['test/testings/', 'test/testings/'],
                 ['/test/testings/', '/test/testings/'],
                 [['test', 'testings'], 'test/testings'],
                 [['test/', 'testings'], 'test/testings'],
                 [['test', '/testings'], 'test/testings'],
                 [['test', 'testings/'], 'test/testings/'],
                 [['/test/', '/testings/'], '/test/testings/'],
             ]);

test('rtrim',
    function($path, $ex) {
        expect(Pathering::rtrim($path))->toBe($ex);
    })->with([
                 ['/test/testings', '/test/testings'],
                 ['/test/testings/', '/test/testings'],

             ]);
test('ltrim',
    function($path, $ex) {
        expect(Pathering::ltrim($path))->toBe($ex);
    })->with([
                 ['test/testings/', 'test/testings/'],
                 ['/test/testings/', 'test/testings/'],

             ]);
test('trim',
    function($path, $ex) {
        expect(Pathering::trim($path))->toBe($ex);
    })->with([
                 ['test/testings/', 'test/testings'],
                 ['/test/testings/', 'test/testings'],

             ]);

test('to string',

    function($paths, $ex) {
        expect(Pathering::concat(...(array)$paths))->toBe($ex);
    })->with([
                 [['test', 'testings'], 'test/testings'],
                 [['test/', 'testings'], 'test/testings'],
                 [['/test/', '/testings/'], '/test/testings'],
                 [['/test/', '/testings/','my-file.php'], '/test/testings/my-file.php'],
             ]);

test('to string expect error',

    function() {
        expect(Pathering::concat('/test/', '', '/testings/'));
    })->expectException(UnexpectedValueException::class);

test('to string expect error  0',

    function() {
        expect(Pathering::concat('/test/', '/', '/testings/'));
    })->expectException(UnexpectedValueException::class);

test('to string expect error 1',

    function() {
        expect(Pathering::concat('/test/', '//', '/testings/'));
    })->expectException(UnexpectedValueException::class);

/**
 * todo bissi creepy ..check $strip and $fromPath that an slash has to be added or not
 */
test('strip path from start',
    function($strip, $fromPath, $ex) {
        expect((string)Pathering::stripPathFromStart($strip, $fromPath))->toBe($ex);
    })->with([
                 ['/var/www/html', '/var/www/html/my-vendor', 'my-vendor'],
                 ['/var/www/html/', '/var/www/html/my-vendor/cutse/', 'my-vendor/cutse'],
             ]);
