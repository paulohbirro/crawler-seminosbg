<?php

namespace App\Http\Controllers;

use Goutte\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @SWG\Info(
 *   version="1.0.0",
 *   title="Api web crawller seminovosBH",
 * )
 *
 */


class CrawllerSeminovos extends Controller
{
   /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */

   public $crawler;
   public $dados = [];


   public function buscarVeiculoFiltro($marca, $modelo, $ano, $preco)
   {
      $client = new Client();
      $crawler = $client->request('GET', "https://seminovos.com.br/carro/$marca/$modelo/ano-$ano/preco-$preco?page=1");
      $this->crawler = $crawler;

      $this->crawler->filter('.card-content')->each(function (Crawler $cardContent, $i) {
         $tituloVeiculo = $cardContent->filter('.card-title')->first()->text();
         $precoVeiculo = $cardContent->filter('.card-price')->first()->text();
         $vercaoVeiculo = $cardContent->filter('.card-subtitle')->first()->text();
         $listInline = $cardContent->filter('.list-features')->first()->text();

         $listInFeatures = $cardContent->filter('.list-inline')->first()->text();

         $listInFeatures = str_replace("\n", "", $listInFeatures);
         $listInFeatures = str_replace("&nbsp;", "", $listInFeatures);
         $listInFeatures = trim($listInFeatures);

         $partsFeatures = explode(",", $listInFeatures);

         $listInline = str_replace("\n", "", $listInline);
         $listInline = str_replace("&nbsp;", "", $listInline);
         $listInline = str_replace("kmManual", "Km Manual", $listInline);
         $listInline = str_replace("kmAutomático", "Km  Automático", $listInline);

         $listInline = trim($listInline);

         $parts = explode(" ", $listInline);


         $vercaoVeiculo = str_replace("\n", "", $vercaoVeiculo);
         $vercaoVeiculo = str_replace("&nbsp;", "", $vercaoVeiculo);
         $vercaoVeiculo = trim($vercaoVeiculo);

         $detalhe = $this->crawler->filter('.card-content >a')->extract(['href']);

         $this->dados[] = array(
            "title" => $tituloVeiculo,
            "price" => $precoVeiculo,
            "version" => $vercaoVeiculo,
            "acessorios" => $parts,
            "features" => $partsFeatures,
            "linkDetalhe" => "https://seminovos.com.br" . $detalhe[$i]
         );
      });
   }

   /**
    * @SWG\Get(
    *     path="/buscar/{marca}/{modelo}/{ano}/{preco}",
    *     summary="Retorna todos veiculos",
    *     tags={"veiculo"},
    *     description="Busca todos veiculos de acordo com os filtros passados",
    *     operationId="findPetsByTags",
    *     produces={"application/xml", "application/json"},
    *     @SWG\Parameter(
    *         name="marca",
    *         in="path",
    *         description="fiat, honda, ford ..",
    *         required=true,
    *         type="string",
    *     ),
    *  @SWG\Parameter(
    *     name="modelo",
    *     in="path",
    *     description="uno, palio, tempra",
    *     required=true,
    *     type="string"
    *   ),
    * *  @SWG\Parameter(
    *     name="ano",
    *     in="path",
    *     description="1981-2020",
    *     required=true,
    *     type="string"
    *   ),
    *  @SWG\Parameter(
    *     name="preco",
    *     in="path",
    *     description="2000-40000",
    *     required=true,
    *     type="string"
    *   ),
    *     @SWG\Response(
    *         response=200,
    *         description="Operação Realizada Com sucesso!",
    *        
    *     ),
    * )
    */
   public function index($marca, $modelo, $ano, $preco)
   {
      try {

         $this->buscarVeiculoFiltro($marca, $modelo, $ano, $preco);
         return response()->json($this->dados, 200);
      } catch (\Throwable $th) {
         return response()->json("Erro, não encontrou nenhum veiculo", 404);
      }
   }
}
