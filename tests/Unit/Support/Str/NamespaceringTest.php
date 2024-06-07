<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */

/** @copyright-header * */

use Filefabrik\Bootraiser\Support\Str\Namespacering;

test(
    'work',
    function() {
        expect((string)\Filefabrik\Bootraiser\Support\Str\Namespacering::work())
            ->toBeEmpty()
            ->and((string)Namespacering::work('test', 'testing'))
            ->toBe('test\\testing')
            ->and((string)Namespacering::work('test', 'testing', 'something'))
            ->toBe('test\\testing\\something')
        ;
    },
);
test(
    'work with end',
    function() {
        expect((string)Namespacering::work())
            ->toBeEmpty()
            ->and(
                Namespacering::withEnd('test', 'testing'),
            )
            ->toBe('test\\testing\\')
            ->and(
                Namespacering::withEnd('test', 'testing', 'something'),
            )
            ->toBe('test\\testing\\something\\')
        ;
    },
);

test(
    'work mixed',
    function() {
        expect((string)Namespacering::work())
            ->toBeEmpty()
            ->and((string)Namespacering::work('\\test\\', 'testing'))
            ->toBe('test\\testing')
            ->and(
                Namespacering::withEnd('\\test\\', 'testing'),
            )
            ->toBe('test\\testing\\')
            ->and(Namespacering::withEnd('\\test\\', '\\testing\\'))
            ->toBe('test\\testing\\')
            ->and(
                Namespacering::withEnd('\\test\\SomeZing\Normal', '\\testing\\'),
            )
            ->toBe('test\\SomeZing\Normal\\testing\\')
        ;
    },
);
test(
    'work with throw',
    function() {
        Namespacering::concat('segment\\', '\\', 'test');
    },
)->throws(UnexpectedValueException::class);
/*
 *
 *todo check test is needed.
test('work with throw2',
	function() {
		(string) Namespacering::work('segment\\\\test','something');
	})->throws(UnexpectedValueException::class);
*/
test('Prefix namespace if need',
    function($prefix, $relativeNamespace, $ex) {
        $res = Namespacering::prefixNamespaceIfNeed($prefix, $relativeNamespace);
        expect($res)->toBe($ex);
    })->with([
                 ['MyNamespace\\Subnamespace', 'Model', 'MyNamespace\\Subnamespace\\Model'],
                 ['MyNamespace\\Subnamespace\\', 'MyNamespace\\Subnamespace\\Model', 'MyNamespace\\Subnamespace\\Model'],
             ]);

test('empty segment throws',
    function() {
    Namespacering::concat('good','','also good');

        expect(  Namespacering::concat('good',' ','also good'));
    })->expectException(UnexpectedValueException::class);


test(
    'rtrim',
    function() {
        expect(Namespacering::rtrim('test\\testing\\'))
            ->toBe('test\\testing')
            ->and(Namespacering::rtrim('test\\testing\\\\'))
            ->toBe('test\\testing')
        ;
    },
);
test(
    'trim',
    function() {
        expect(Namespacering::trim('\\test\\testing\\'))
            ->toBe('test\\testing')
            ->and(Namespacering::trim('\\test\\testing\\\\'))
            ->toBe('test\\testing')
        ;
    },
);
