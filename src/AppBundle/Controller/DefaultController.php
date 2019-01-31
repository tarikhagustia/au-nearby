<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

// ...
use AppBundle\Entity\City;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
      $nearest = [];
      $cities = $this->getDoctrine()
        ->getRepository(City::class)->findAll();
        // replace this example code with whatever you need
        $selectedCity = null;
        $params = $request->query->all();
        if ($request->get('city')) {
          $selectedCity = $this->getDoctrine()
            ->getRepository(City::class)->find($request->get('city'));

          $lat = $selectedCity->getLatitude();
          $long = $selectedCity->getLongitude();
          // dump($selectedCity);
          $distance = $request->get('distance');
                  $sql = "
                  SELECT id, name, latitude, longitude, postcode, state, (3956 * 2 * ASIN(SQRT( POWER(SIN(( $lat - latitude) *  pi()/180 / 2), 2) +COS( $lat * pi()/180) * COS(latitude * pi()/180) * POWER(SIN(( $long - longitude) * pi()/180 / 2), 2) ))) as distance
                  from postcodes_geo
                  having  distance <= $distance
                  order by distance";

            $em = $this->getDoctrine()->getManager();
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->execute();
            $nearest = $stmt->fetchAll();

        }
        $json_nearest = json_encode($nearest);
        return $this->render('default/index.html.twig', compact('cities', 'params', 'nearest', 'json_nearest', 'selectedCity'));
    }

    /**
     * @Route("/get-weather", name="get_weather")
     */
    public function getWeatherAction(Request $request)
    {
      $city_id = $request->get('city_id');
      $selectedCity = $this->getDoctrine()
        ->getRepository(City::class)->find($city_id);

      if ($selectedCity) {
        $response = file_get_contents('http://api.openweathermap.org/data/2.5/weather?units=metric&zip='.$selectedCity->getPostcode().',au&appid=1860ad5a8129c07c4451b88a84b4d2d9');

        $response = json_decode($response);

        $data = [
          'name' => $selectedCity->getName(),
          'postcode' => $selectedCity->getPostcode(),
          'temp' => $response->main->temp,
          'humidity' => $response->main->humidity,
          'pressure' => $response->main->pressure,
        ];
        // return new Response($response);
        return $this->json($data);
      }
    }

    /**
     * @Route("/get-cities", name="get_cities")
     */
    public function getCitiesAction(Request $request)
    {
      $em = $this->getDoctrine()->getManager();
      $name = strtolower($request->get('q'));
      $repository = $em->getRepository(City::class);
      $query = $repository->createQueryBuilder('p')
               ->where('p.name LIKE LOWER(:word)')
               ->setParameter('word', '%'.$name.'%')
               ->getQuery();
      $cities = $query->getResult();
      $result = [];
      foreach ($cities as $key => $city) {
        $result[] = [
          'label' => $city->getPostcode() . ' '. $city->getName(),
          'value' => $city->getPostcode() . ' '. $city->getName(),
          'id' => $city->getId()
        ];
      }

      return $this->json($result);
    }
}
