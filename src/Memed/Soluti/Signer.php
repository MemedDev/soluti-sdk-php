<?php

declare(strict_types=1);

namespace Memed\Soluti;

use App\Memed\DigitalSignature\Contracts\SignatureOptionsContract;
use GuzzleHttp\Psr7\Response;
use Memed\Soluti\Auth\CloudAuthentication;
use Memed\Soluti\Dto\SignableDocumentSet;
use Memed\Soluti\Dto\SignaturePayload;
use Memed\Soluti\Dto\SignatureResponse;
use Memed\Soluti\Dto\SignedDocumentSet;
use Memed\Soluti\Http\Request;

class Signer
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * Constructor.
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Sends given document object to be signed in Soluti service using given
     * strategy.
     *
     * @param SignatureOptionsContract $signatureOptions
     * @param SignableDocumentSet      $signableDocumentSet
     * @return SignatureResponse
     */
    public function sign(
        SignatureOptionsContract $signatureOptions,
        SignableDocumentSet $signableDocumentSet
    ): SignatureResponse {
        $documents = [];
        foreach ($signableDocumentSet as $key => $signableDocument) {
            $documents[$key] = $signableDocument->toArray();
        }

        $payload = SignaturePayload::create(
            [
                'certificate_alias' => $signatureOptions->getCertificateAlias(),
                'signature_settings' => $signatureOptions->signatureSettings(),
                'documents' => $documents,
            ]
        );

        $request = new Request(
            'post',
            $this->manager->cessUrl(CloudAuthentication::CESS_SIGNATURE_SERVICE_URL),
            $payload->toArray(),
            [
                'Authorization' => $signatureOptions->getSignToken()->toVCSchema(),
            ]
        );

        return $this->getResponse($this->manager->client()->json($request));
    }

    public function getResponse(Response $response): SignatureResponse
    {
        $responseData = json_decode((string) $response->getBody(), true);
        $responseData['documents'] = SignedDocumentSet::create($responseData['documents']);
        $responseData['transaction-cookie'] = $this->manager
            ->client()
            ->getCookies()
            ->getCookieByName(
                config(
                    'signature.providers.soluti.transaction-cookie',
                    'default'
                )
            );

        return SignatureResponse::create($responseData);
    }
}
