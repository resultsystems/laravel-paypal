<?php

namespace ResultSystems\Paypal\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Mail;
use ResultSystems\Paypal\Customer;
use ResultSystems\Paypal\Http\Controllers\Controller;
use ResultSystems\Paypal\Ipn;
use ResultSystems\Paypal\Services\PaypalRequestService;
use ResultSystems\Paypal\Transaction;

class PaypalController extends Controller
{
    private $customer;
    private $ipn;
    private $transaction;

    /**
     * Insere as dependências.
     *
     * DESCULPE-ME PELA FALTA DE UM REPOSITÓRIO:
     *
     * POREM NÃO VEJO NECESSIDADE DE CRIAR UM REPOSITÓRIO,
     * PENAS PARA SALVAR UM ITEM, QUANDO AS NECESSIDADES CRESCER
     * AI SIM, VEREI A QUESTÃO DE CRIAR OS REPOSITÓRIO DE CADA CLASSE
     * POR ENQUANTO VAMOS USAR AS MODEL NO CONTROLER MESMO
     * ANTES QUE ME CRUCIFIQUEM POR NÃO SEGUIR CERTAS REGRAS
     *
     * @param Customer    $customer
     * @param Ipn         $ipn
     * @param Transaction $transaction
     */
    public function __construct(Customer $customer, Ipn $ipn, Transaction $transaction)
    {
        $this->customer = $customer;
        $this->ipn = $ipn;
        $this->transaction = $transaction;
    }
    public function ipn(PaypalRequestService $paypal, Request $request)
    {
        $input = $request->all();

        // dd($input);
        $data['data'] = json_encode($input);

        try {
            $r = Mail::send('emails.ipn', $data, function ($message) {
                $message
                    ->replyTo('hlhenrique@gmail.com')
                    ->to(env('MAIL_TO'))
                    ->subject('Conctact: Paypal IPN!');
            });
        } catch (Exception $e) {
        }

        if (!$paypal->isIPNValid($input)) {
            return response()->json('Algo errado', 403);
        }

        //Está tudo correto, somos o destinatário da notificação, vamos
        //gravar um log dessa notificação.

        $ipn = new $this->ipn;
        $ipn->fill($input);
        try {
            $ipn->save();
        } catch (Exception $e) {
        }

        //Log gravado, podemos seguir com as regras de negócio para
        //essa notificação.

        //gravamos dados do cliente
        $customer = new $this->customer;
        $customer->fill($input);
        try {
            if (!isset($input['email']) && isset($input['payer_email'])) {
                $input['email'] = $input['payer_email'];
            }
            $customer->save();
        } catch (Exception $e) {
        }

        //gravamos dados da transação
        $transaction = new $this->transaction;
        $transaction->fill($input);
        try {
            $transaction->save();
        } catch (Exception $e) {
        }

        return response()->json('Log travado');
    }
}
