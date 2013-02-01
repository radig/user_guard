<?php
App::uses('CakeTestSuite', 'TestSuite');
class AllUserGuardTestsTest extends CakeTestSuite
{
    public static function suite()
    {
        $suite = new CakeTestSuite('All UserGuard Tests');

        $suite->addTestDirectory(__DIR__ . '/Model/Behavior');

        return $suite;
    }
}
