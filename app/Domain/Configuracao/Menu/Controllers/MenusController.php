<?php

namespace App\Domain\Configuracao\Menu\Controllers;

use App\Domain\Configuracao\Menu\Models\Item;
use App\Domain\Configuracao\Menu\Models\Modulo;
use App\Domain\Configuracao\Menu\Requests\SalvarMenuRequest;
use App\Domain\Configuracao\Menu\Resources\ItensResource;
use App\Domain\Configuracao\Menu\Resources\ModulosResource;
use App\Domain\Configuracao\Menu\Services\MenusService;
use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Http\Controllers\Controller;

/**
 * @apiDefine usuario Usuario access only
 * Requisicao deve ser feita por um usuario logado
 */
/**
 * @apiDefine Error
 * @apiError {[]} data
 * @apiError {true} error Indica que ocorreu um erro
 * @apiError {String} message Mensagem de erro
 */
class MenusController extends Controller
{
    /**
     * @return DBJsonResponse
     *
     * @api {get} configuracao/menu/modulos 1 - Buscar modulos
     * @apiName BuscarModulos
     * @apiGroup Configuracao-Menu
     * @apiPermission usuario
     *
     * @apiSuccess {Object[]} data Array de modulos
     * @apiSuccess {Integer} data.codigo codigo do modulo
     * @apiSuccess {String} data.descricao descricao do modulo
     * @apiSuccess {false} error Indica que nao ocorreu erro
     *
     * @apiUse Error
     */
    public function getModulos(MenusService $service)
    {
        $modulos = $service->getModulos();

        return new DBJsonResponse(ModulosResource::toResponse($modulos));
    }

    /**
     * @return DBJsonResponse
     *
     * @api {get} configuracao/menu/modulos/:id/itens 2 - Buscar itens a partir de um modulo
     * @apiName BuscarItensModulo
     * @apiGroup Configuracao-Menu
     * @apiPermission usuario
     *
     * @apiParam {Integer} id Codigo do modulo
     *
     * @apiSuccess {Object[]} data Array de itens
     * @apiSuccess {Integer} data.key codigo do item
     * @apiSuccess {String} data.label descricao do item
     * @apiSuccess {Object[]} data.children Array com itens filhos
     * @apiSuccess {false} error Indica que nao ocorreu erro
     *
     * @apiUse Error
     */
    public function getItens(Modulo $modulo, MenusService $service)
    {
        $menus = $service->getArvoreMenu($modulo->id_item, $modulo->id_item);

        return new DBJsonResponse(ItensResource::toTreeNode($menus));
    }

    /**
     * @return DBJsonResponse
     * @throws \Exception
     *
     * @api {post} configuracao/menu/modulos/:modulo/itens/:item 3 - Cadastrar item de menu
     * @apiName CadastrarItemModulo
     * @apiGroup Configuracao-Menu
     * @apiPermission usuario
     *
     * @apiParam {Integer} modulo Codigo do modulo em que o item sera cadastrado
     * @apiParam {Integer} item Codigo do item em que o item sera cadastrado
     *
     * @apiBody {String} descricao Descricao do item
     * @apiBody {String} ajuda Ajuda sobre a funcionalidade do item
     * @apiBody {String} descricaoTecnica Descricao mais tecnica e detalhada sobre o item
     * @apiBody {String} rota Rota/view a ser exibida
     * @apiBody {Boolean} liberadoCliente Determina se o item ira ser exibido ou nao
     *
     * @apiSuccess {String} message Mensagem de sucesso
     * @apiSuccess {false} error Indica que nao ocorreu erro
     *
     * @apiUse Error
     */
    public function salvar(Modulo $modulo, Item $item, SalvarMenuRequest $request, MenusService $service)
    {
        $service->salvar($modulo->id_item, $item->id_item, $request->all());

        return new DBJsonResponse([], 'Menu salvo com sucesso.', 201);
    }
}
