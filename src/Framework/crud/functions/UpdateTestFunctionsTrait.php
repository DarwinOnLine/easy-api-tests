<?php

namespace EasyApiTests\crud\functions;

use EasyApiBundle\Util\ApiProblem;
use Symfony\Component\HttpFoundation\Response;

trait UpdateTestFunctionsTrait
{
    use crudFunctionsTestTrait;

    /**
     * PUT - Nominal case.
     * @param int|null $id
     * @param string $filename
     * @param array $params
     * @param string|null $userLogin
     * @param bool $doGetTest
     * @param int $expectedResponseCode
     * @throws \Exception
     */
    protected function doTestUpdate(?int $id, string $filename, array $params = [], string $userLogin = null, bool $doGetTest = true, int $expectedResponseCode = Response::HTTP_OK): void
    {
        $id = $id ?? static::defaultEntityId;
        $params += ['id' => $id];
        $data = $this->getDataSent($filename, self::$updateActionType);

        // Request
        $apiOutput = self::httpPutWithLogin(['name' => static::getUpdateRouteName(), 'params' => $params], $userLogin, $data);

        // Assert result
        static::assertEquals($expectedResponseCode, $apiOutput->getStatusCode());

        // verify response code & response content
        if (Response::HTTP_NO_CONTENT !== $expectedResponseCode) {
            $result = $apiOutput->getData();
            $expectedResult = $this->getExpectedResponse($filename, 'Update', $result, true);
            static::assertAssessableContent($expectedResult, $result);
            static::assertEquals($expectedResult, $result, "Assert failed for file {$filename}");
        } else {
            static::assertEmpty($apiOutput->getData(true));
        }

        // Get after put
        if ($doGetTest) {
            $apiOutput = self::httpGetWithLogin(['name' => static::getGetRouteName(), 'params' => ['id' => $id]], $userLogin);
            static::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
            $result = $apiOutput->getData();
            $expectedResult = $this->getExpectedResponse($filename, 'Update', $result, true);
            static::assertAssessableContent($expectedResult, $result);
            static::assertEquals($expectedResult, $result, "Assert failed for get after put for file {$filename}");
        }
    }

    /**
     * Test Invalid submitted data case, fox example invalid data in a field with constraint
     * @param int|null $id
     * @param string $filename
     * @param array $params
     * @param array $expectedErrors
     * @param int $expectedStatusCode
     * @param string|null $userLogin

     * @throws \Exception
     */
    protected function doTestUpdateInvalid(?int $id, string $filename, array $params = [], array $expectedErrors, int $expectedStatusCode = Response::HTTP_UNPROCESSABLE_ENTITY, string $userLogin = null): void
    {
        $id = $id ?? static::defaultEntityId;
        $params += ['id' => $id];
        $data = $this->getDataSent($filename, self::$updateActionType);
        $apiOutput = self::httpPutWithLogin(['name' => static::getUpdateRouteName(), 'params' => $params], $userLogin, $data);
        static::assertApiProblemError($apiOutput, $expectedStatusCode, $expectedErrors);
    }

    /**
     * GET - Error case - entity not found.
     * @param int|null $id
     * @param array $params
     * @param string|null $userLogin

     */
    public function doTestUpdateNotFound(int $id = null, array $params = [], string $userLogin = null): void
    {
        $params += ['id' => $id ?? 99999999];
        $apiOutput = self::httpPutWithLogin(['name' => static::getUpdateRouteName(), 'params' => $params], $userLogin, []);
        static::assertApiProblemError($apiOutput, Response::HTTP_NOT_FOUND, [sprintf(ApiProblem::ENTITY_NOT_FOUND, 'entity')]);
    }

    /**
     * PUT - Error case - 401 - Without authentication.
     * @param int|null $id
     * @param array $params
     */
    protected function doTestUpdateWithoutAuthentication(int $id = null, array $params = []): void
    {
        $params += ['id' => $id ?? static::defaultEntityId];
        $apiOutput = self::httpPut(['name' => static::getUpdateRouteName(), 'params' => $params], [], false);
        static::assertApiProblemError($apiOutput, Response::HTTP_UNAUTHORIZED, [ApiProblem::JWT_NOT_FOUND]);
    }

    /**
     * PUT - Error case - 403 - Missing right.
     * @param int|null $id
     * @param array $params
     * @param string|null $userLogin

     * @throws \Exception
     */
    protected function doTestUpdateWithoutRight(int $id = null, array $params = [], string $userLogin = null): void
    {
        $params += ['id' => $id ?? static::defaultEntityId];

        if (null === $userLogin) {
            $userLogin = static::USER_NORULES_TEST_USERNAME;
        }

        $apiOutput = self::httpPutWithLogin(['name' => static::getUpdateRouteName(), 'params' => $params], $userLogin, []);

        static::assertApiProblemError($apiOutput, Response::HTTP_FORBIDDEN, [ApiProblem::RESTRICTED_ACCESS]);
    }

    /**
     * PUT - Error case - 403 - Forbidden action.
     * @param int|null $id
     * @param string|null $filename
     * @param array $params
     * @param string|null $userLogin

     * @param array $messages
     * @param int $errorCode
     * @throws \Exception
     */
    protected function doTestUpdateForbiddenAction(int $id = null, string $filename = null, array $params = [], string $userLogin = null, $messages = [ApiProblem::FORBIDDEN], $errorCode = Response::HTTP_FORBIDDEN): void
    {
        $params += ['id' => $id ?? static::defaultEntityId];

        $data = null != $filename ? $this->getDataSent($filename, self::$updateActionType) : [];

        $apiOutput = self::httpPutWithLogin(['name' => static::getUpdateRouteName(), 'params' => $params], $userLogin, $data);

        static::assertApiProblemError($apiOutput, $errorCode, $messages);
    }
}
