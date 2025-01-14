<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Service\AnalyticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DataController extends AbstractController
{
    private AnalyticsService $analyticsService;

    /**
     * DataController constructor.
     * @param AnalyticsService $analyticsService
     */
    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * @Route("/api/skelbimai", name="ads_data")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $this->analyticsService->logVisit($request);
        $offset = $request->get('offset') ? $request->get('offset') : 0;
        $limit = $request->get('limit') ? $request->get('limit') : 100;
        $search = $request->get('search') ? $request->get('search') : '';

        $ads = $this->getDoctrine()->getRepository(Ad::class)->getAds($offset, $limit, $search);
        $filteredAdsCount = $this->getDoctrine()->getRepository(Ad::class)->getAdsBySearchCount($search);
        $adsCount = $this->getDoctrine()->getRepository(Ad::class)->count([]);

        $data = [
            'total' => $filteredAdsCount,
            'totalNotFiltered' => $adsCount,
            'rows' => $this->adsEntityArrayToArray($ads)
        ];

        return $this->json($data);
    }

    /**
     * @param Ad[] $ads
     * @return array
     */
    private function adsEntityArrayToArray(array $ads): array
    {
        $array = [];

        foreach ($ads as $ad) {
            $array[] = [
                'datetime' => $ad->getDatetime()->format('Y-m-d H:i:s'),
                'reporter' => $ad->getReporter(),
                'text' => $ad->getText()
            ];
        }

        return $array;
    }
}
