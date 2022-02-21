<?php

namespace EasyApiTests\src\Util\crud;

use EasyApiTests\src\Util\crud\functions\GetListTestFunctionsTrait;

trait GetListTestTrait
{
    use GetListTestFunctionsTrait;

    protected static $paginationPage = 2;
    protected static $paginationLimit = 3;

    protected static function initExecuteSetupOnAllTest()
    {
        static::$executeSetupOnAllTest = false;
    }

    /**
     * GET - Nominal case.
     */
    public function testGet(): void
    {
        $this->doTestGetList('nominalCase.json');
    }

    /**
     * GET - Nominal case.
     */
    public function testGetAll(): void
    {
        $this->doTestGetListPaginate('getAll.json', 1, 99999);
    }

    /**
     * GET - Page Limit .
     */
    public function testGetPageLimit(): void
    {
        $this->doTestGetListPaginate('page'.static::$paginationPage.'Limit'.static::$paginationPage.'.json', static::$paginationPage, static::$paginationLimit);
    }

    /**
     * GET - Error case - 401 - Without authentication.
     */
    public function testGetWithoutAuthentication(): void
    {
        $this->doTestGetWithoutAuthentication();
    }

    /**
     * GET - Error case - 403 - Missing ADMIN role.
     */
    public function testGetWithoutRight(): void
    {
        $this->doTestGetWithoutRight();
    }
}
