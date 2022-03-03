<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableVatExemptionReasons extends Migration
{
    public function up()
    {
        Schema::create('vat_exemption_reasons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->string('name');
            $table->text('description');
            $table->timestamps();
        });

        DB::table('vat_exemption_reasons')->insert([
            ['code' => 'M01', 'description' => 'Quantias pagas em nome e por conta do adquirente dos bens ou do destinatário dos serviços, registadas pelo sujeito passivo em contas de terceiros apropriadas.', 'name' => 'Artigo 16.º n.º 6 alínea c) do CIVA'],
            ['code' => 'M02', 'description' => 'Vendas de mercadorias de valor superior a 1.000 €/factura, efectuadas por um fornecedor a um exportador nacional, exportadas no mesmo estado (ver regras aplicáveis).', 'name' => 'Artigo 6.º do Decreto‐Lei n.º 198/90, de 19 de Junho'],
            ['code' => 'M03', 'description' => 'Exigibilidade da liquidação do IVA no momento do recebimento total ou parcial das facturas emitidas aos clientes, embora adie o direito à dedução do imposto até ao momento do pagamento aos respectivos fornecedores.', 'name' => 'Exigibilidade de caixa'],
            ['code' => 'M04', 'description' => 'Certo tipo de importações ou reimportações. (ver artigo para mais detalhes)', 'name' => 'Isento Artigo 13.º do CIVA'],
            ['code' => 'M05', 'description' => 'Exportações, operações assimiladas e transportes internacionais.', 'name' => 'Isento Artigo 14.º do CIVA'],
            ['code' => 'M06', 'description' => 'Operações relacionadas com regimes suspensivos. (ver lista completa no artigo respectivo)', 'name' => 'Isento Artigo 15.º do CIVA'],
            ['code' => 'M07', 'description' => 'Variadas actividades referentes à saúde, apoio social, artes & espectácultos, seguros, locação de espaços, lotarias e apostas devidamente autorizadas e outras. (ver lista completa no artigo respectivo)', 'name' => 'Isento Artigo 9.º do CIVA'],
            ['code' => 'M08', 'description' => 'Quando o destinatário ou adquirente for o devedor do imposto, ou seja, o transmitente dos bens/serviços deve emitir as facturas sem a respectiva liquidação do IVA, e o adquirente dos produtos/serviços, dentro dos mesmos prazos, deve realizar a autoliquidação do imposto.', 'name' => 'IVA – Autoliquidação'],
            ['code' => 'M09', 'description' => 'Retalhistas que sejam pessoas singulares, não possuam nem sejam obrigados a possuir contabilidade organizada para efeitos do IRS e não tenham tido no ano civil anterior um volume de compras superior a 50.000 €, para apurar o imposto devido ao Estado, aplicam um coeficiente de 25% ao valor do imposto suportado nas aquisições de bens destinados a vendas sem transformação.', 'name' => 'IVA ‐ não confere direito a dedução'],
            ['code' => 'M10', 'description' => 'Sujeitos passivos que, não possuindo nem sendo obrigados a possuir contabilidade organizada para efeitos do IRS ou IRC, nem praticando operações de importação, exportação ou actividades conexas, nem exercendo actividade que consista na transmissão dos bens ou prestação dos serviços mencionados no anexo E do Código de IVA, não tenham atingido, no ano civil anterior, um volume de negócios superior a 10.000 € ou entre 10.000 e 12.500 € que se tributados se enquadrariam em pequenos retalhistas.', 'name' => 'IVA – Regime de isenção'],
            ['code' => 'M11', 'description' => 'Produtores e revendedores de tabaco. (ver condições específicas no referido Decreto-Lei)', 'name' => 'Regime particular do tabaco'],
            ['code' => 'M12', 'description' => 'Operações das agências de viagens e organizadores de circuitos turísticos que actuem em nome próprio perante os clientes e recorram, para a realização dessas operações, a transmissões de bens ou a prestações de serviços efectuadas por terceiros. O imposto cobrado ao utente, no país da sede ou estabelecimento estável da agência, incide apenas sobre a «margem bruta» da mesma. (Ver normas específicas no referido Decreto-Lei)', 'name' => 'Regime da margem de lucro - Agências de Viagens'],
            ['code' => 'M13', 'description' => 'Estão sujeitas a IVA, segundo o regime especial de tributação da margem, transmissões de bens em segunda mão, efectuadas nos termos deste diploma, por sujeitos passivos revendedores ou por organizadores de vendas em leilão que actuem em nome próprio, por conta de um comitente ou de acordo com um contrato de comissão de venda.', 'name' => 'Regime da margem de lucro - Bens em segunda mão'],
            ['code' => 'M14', 'description' => 'Estão sujeitas a IVA, segundo o regime especial de tributação da margem, transmissões de objectos de arte, efectuadas nos termos deste diploma, por sujeitos passivos revendedores ou por organizadores de vendas em leilão que actuem em nome próprio, por conta de um comitente ou de acordo com um contrato de comissão de venda.', 'name' => 'Regime da margem de lucro - Objetos de arte'],
            ['code' => 'M15', 'description' => 'Estão sujeitas a IVA, segundo o regime especial de tributação da margem, transmissões de colecção e de antiguidades, efectuadas nos termos deste diploma, por sujeitos passivos revendedores ou por organizadores de vendas em leilão que actuem em nome próprio, por conta de um comitente ou de acordo com um contrato de comissão de venda.', 'name' => 'Regime da margem de lucro - Objetos de coleção e antiguidades'],
            ['code' => 'M16', 'description' => 'As transmissões de bens, efectuadas por um sujeito passivo, expedidos ou transportados pelo vendedor, pelo adquirente ou por conta destes (com NIF validado no VIES), a partir do território nacional para outro Estado membro com destino ao adquirente, quando este seja uma pessoa singular ou colectiva registada para efeitos do imposto sobre o valor acrescentado em outro Estado membro.', 'name' => 'Isento Artigo 14.º do RITI'],
            ['code' => 'M99', 'description' => 'Ver outras situações abrangidas por isenções nos artigos indicados.', 'name' => 'Não sujeito; não tributado (ou similar)'],
        ]);        

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->integer('vat_exemption_reason_id')->unsigned()->nullable();

            $table->foreign('vat_exemption_reason_id')->references('id')->on('vat_exemption_reasons');
        });

        DB::table('vat_types')->insert([
            ['code' => 'NO', 'name' => 'Regime de isenção de IVA', 'percent' => 0],
        ]);

        DB::table('payment_conditions')->insert([
            ['name' => 'Fatura a 15 dias', 'days' => 15],
            ['name' => 'Fatura a 30 dias', 'days' => 30],
            ['name' => 'Fatura a 45 dias', 'days' => 45],
            ['name' => 'Fatura a 60 dias', 'days' => 60],
            ['name' => 'Fatura a 120 dias', 'days' => 120],
            ['name' => 'Prestações', 'days' => 0],
        ]);

        DB::table('movement_types')->insert([
            ['name' => 'AMORT', 'description' => 'amortização de emprestimo', 'days' => 0],
            ['name' => 'DTREC', 'description' => 'Desconto de Titulos a Receber', 'days' => 3],
            ['name' => 'DVC', 'description' => 'Diversos a crédito', 'days' => 1],
            ['name' => 'GARAN', 'description' => 'Garantias bancárias', 'days' => 0],
            ['name' => 'JRC', 'description' => 'Juros Credores', 'days' => 1],
            ['name' => 'MB', 'description' => 'Rec. por Multibanco', 'days' => 0],
            ['name' => 'NUM', 'description' => 'Rec. em Numerário', 'days' => 0],
            ['name' => 'SLA', 'description' => 'Saldo abertura', 'days' => 0],
        ]);

        Schema::table('global_settings', function (Blueprint $table) {
            $table->integer('vat_exemption_reason_id')->nullable();
        });

    }

    public function down()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['vat_exemption_reason_id']); 

            $table->dropColumn('vat_exemption_reason_id');
        });
        Schema::dropIfExists('vat_exemption_reasons');

        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn('vat_exemption_reason_id');
        });
    }
}
