<?php

namespace App\Http\Controllers\Quote;

use App\Http\Controllers\Controller;
use App\Http\Models\Validators\TaxAdjustValidator;
use App\Http\Models\Validators\TaxCommitValidator;
use App\Http\Models\Validators\TaxVoidValidator;
use App\Services\TaxEstimationService;
use App\Http\Models\Validators\TaxEstimateValidator;
use App\Http\Builders\TaxResponseBuilder;
use http\Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    const BC_HEADER = 'X-BC-Store-Hash';
    const ERROR_INCORRECT_HEADERS = 'Incorrect headers provided';
    const ERROR_BADLY_FORMATTED = 'Badly Formatted request';
    const SAMPLE_TAX = 'SampleTax';
    const ID = 'id';

    /** @var TaxEstimateValidator */
    private $taxEstimateValidator;

    /** @var TaxEstimationService */
    private $taxEstimationService;

    /** @var TaxAdjustValidator */
    private $taxAdjustValidator;

    /** @var TaxCommitValidator */
    private $taxCommitValidator;

    /** @var TaxVoidValidator */
    private $taxVoidValidator;

    /**
     * @param TaxAdjustValidator $taxAdjustValidator
     * @param TaxCommitValidator $taxCommitValidator
     * @param TaxEstimateValidator $taxEstimateValidator
     * @param TaxEstimationService $taxEstimationService
     * @param TaxVoidValidator $taxVoidValidator
     */
    public function __construct(
        TaxAdjustValidator $taxAdjustValidator,
        TaxCommitValidator $taxCommitValidator,
        TaxEstimateValidator $taxEstimateValidator,
        TaxEstimationService $taxEstimationService,
        TaxVoidValidator $taxVoidValidator
    ) {
        $this->taxEstimateValidator = $taxEstimateValidator;
        $this->taxEstimationService = $taxEstimationService;
        $this->taxAdjustValidator = $taxAdjustValidator;
        $this->taxCommitValidator = $taxCommitValidator;
        $this->taxVoidValidator = $taxVoidValidator;
    }

    public function estimateAction(Request $request)
    {
        if (!$request->headers->get(self::BC_HEADER)) {
            return new JsonResponse($this->buildErrorResponseBody(self::ERROR_INCORRECT_HEADERS));
        }
        $requestPayload = json_decode($request->getContent(), true);
        if (!$this->taxEstimateValidator->validateEstimatePayload($requestPayload)) {
            return new JsonResponse($this->buildErrorResponseBody(self::ERROR_BADLY_FORMATTED));
        }
        try {
            $estimate = $this->taxEstimationService->getEstimate($requestPayload);
            $result[TaxResponseBuilder::DOCUMENTS][] = $estimate;
            $result[self::ID] = self::SAMPLE_TAX . rand();
        } catch (Exception $e) {
            return new JsonResponse($this->buildErrorResponseBody($e->getMessage()));
        }

        $response = new JsonResponse($result);
        $response->setEncodingOptions(JSON_PRESERVE_ZERO_FRACTION);

        return $response;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function commitAction(Request $request): JsonResponse
    {
        if (!$request->headers->get(self::BC_HEADER)) {
            return new JsonResponse($this->buildErrorResponseBody(self::ERROR_INCORRECT_HEADERS));
        }
        $requestPayload = json_decode($request->getContent(), true);
        if (!$this->taxCommitValidator->validateCommitPayload($requestPayload)) {
            return new JsonResponse($this->buildErrorResponseBody(self::ERROR_BADLY_FORMATTED));
        }
        try {
            $commit = $this->taxEstimationService->commitQuote($requestPayload);
            $result[TaxResponseBuilder::DOCUMENTS][] = $commit;
            $result[self::ID] = self::SAMPLE_TAX . rand();
        } catch (Exception $e) {
            return new JsonResponse($this->buildErrorResponseBody($e->getMessage()));
        }

        $response = new JsonResponse($result);
        $response->setEncodingOptions(JSON_PRESERVE_ZERO_FRACTION);

        return $response;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function adjustAction(Request $request): JsonResponse
    {
        if (!$request->headers->get(self::BC_HEADER)) {
            return new JsonResponse($this->buildErrorResponseBody(self::ERROR_INCORRECT_HEADERS));
        }
        $id = $request->id;
        $requestPayload = json_decode($request->getContent(), true);

        if (!$this->taxAdjustValidator->validateAdjustPayload($requestPayload, $id)) {
            return new JsonResponse($this->buildErrorResponseBody(self::ERROR_BADLY_FORMATTED));
        }
        try {
            $commit = $this->taxEstimationService->adjustQuote($requestPayload, $id);
            $result[TaxResponseBuilder::DOCUMENTS][] = $commit;
            // This is temporarily disabled until BC can handle this extra data.
//            $result['adjust_description'] = $requestPayload['adjust_description'] ?? 'no reason provided';
            $result[self::ID] = self::SAMPLE_TAX . rand();
        } catch (Exception $e) {
            return new JsonResponse($this->buildErrorResponseBody($e->getMessage()));
        }

        $response = new JsonResponse($result);
        $response->setEncodingOptions(JSON_PRESERVE_ZERO_FRACTION);

        return $response;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function voidAction(Request $request): JsonResponse
    {
        $id = $request->get('id');
        if (!$this->taxVoidValidator->validate($id)) {
            return new JsonResponse($this->buildErrorResponseBody('No Id Provided'));
        }
        $this->taxEstimationService->void();

        $value = [
            'success' => true

        ];
        return new JsonResponse($value);
    }

    /**
     * @param string $message
     * @return array
     */
    private function buildErrorResponseBody(string $message): array
    {
        return [
            'messages' => [
                [
                    'text' => $message,
                    'type' => 'ERROR',
                ]
            ]
        ];
    }
}