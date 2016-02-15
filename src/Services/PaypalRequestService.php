<?php

namespace ResultSystems\Paypal\Services;

use Config;
use ResultSystems\Paypal\Http\Exceptions\PaypalRequestException;
use ResultSystems\Paypal\PaypalItem;

class PaypalRequestService
{
    protected $username;
    protected $password;
    protected $signature;
    protected $items        = [];
    protected $currencyCode = null;
    protected $certificate  = false;
    protected $certificateFile;
    protected $localeCode = 'pt_BR';

    /**
     * Número da fatura.
     *
     * @var int
     */
    protected $invoice;

    /**
     * sandbox ou produção?
     *
     * @var bool
     */
    protected $sandbox = false;

    /**
     * Constrói a classe de requisição de pagamento.
     *
     * @param int|string       $invoice    número da fatura
     * @param string    $user       usuário
     * @param string    $pass       senha
     * @param string    $signature  assinatura
     */
    public function __construct($invoice = null, $user = null, $pass = null, $signature = null)
    {
        $this->invoice   = $invoice;
        $this->username  = $user;
        $this->password  = $pass;
        $this->signature = $signature;

        if (is_null($user)) {
            $this->username = Config::get('paypal.username', null);
        }

        if (is_null($pass)) {
            $this->password = Config::get('paypal.password', null);
        }

        if (is_null($signature)) {
            $this->signature = Config::get('paypal.signature', null);
        }
    }

    /**
     * Adiciona itens.
     *
     * @param PaypalItem $item
     */
    public function addItem(PaypalItem $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Cria um item e adiciona ao array de itens.
     *
     * @param float     $quantity       Quantidade de produtos
     * @param string    $name           Nome do produto
     * @param string    $description    Descrição do produto
     * @param float     $amount         Valor do produto
     */
    public function setItem($quantity, $name, $description, $amount)
    {
        $this->addItem(new PaypalItem($quantity, $name, $description, $amount));

        return $this;
    }

    /**
     * Pega o código da moeda.
     *
     * @return string
     */
    protected function getCurrencyCode()
    {
        if (!is_null($this->currencyCode)) {
            return $this->currencyCode;
        }

        return Config::get('paypal.currencyCode', 'BRL');
    }

    /**
     * Pega o total dos itens.
     *
     * @return float
     */
    protected function getTotal()
    {
        if (count($this->items) < 1) {
            return 0;
        }

        $total = array_sum(array_map(function ($item) {
            if ($item->amount > 0) {
                return ($item->amount * $item->quantity);
            }

            return 0;
        }, $this->items));

        if ($total > 0) {
            return $total;
        }
        throw new PaypalRequestException('Total is not valid');
    }

    protected function getItems()
    {
        if (count($this->items) < 1) {
            throw new PaypalRequestException('No items');
        }

        $items = [];
        foreach ($this->items as $key => $item) {
            $items['L_PAYMENTREQUEST_0_NAME' . $key] = $item->name;
            $items['L_PAYMENTREQUEST_0_DESC' . $key] = $item->description;
            $items['L_PAYMENTREQUEST_0_AMT' . $key]  = $item->amount;
            $items['L_PAYMENTREQUEST_0_QTY' . $key]  = $item->quantity;
        }
        // $items['L_PAYMENTREQUEST_0_ITEMAMT'] = $this->getTotal();

        return $items;
    }

    /**
     * Pega a url de redirecionamento do paypal.
     * @return string
     */
    protected function getPaypalUrl()
    {
        if ($this->sandbox) {
            //URL da PayPal para redirecionamento, não deve ser modificada
            return 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }

        //URL da PayPal para redirecionamento, não deve ser modificada
        return 'https://www.paypal.com/cgi-bin/webscr';
    }

    /**
     * Pega a url para checkout
     * Se for passado uma closure
     * Executa ela informando o resultado para ela.
     *
     * @param  closure $callback url para redirecionar caso falhe
     *
     * @return closure
     */
    public function getCheckoutUrl($callback = null)
    {
        $requestNvp = $this->getCredentials();
        $requestNvp = array_merge($requestNvp, [
            'METHOD'                         => 'SetExpressCheckout',
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'SALE',
            'PAYMENTREQUEST_0_AMT'           => (string) $this->getTotal(),
            'PAYMENTREQUEST_0_CURRENCYCODE'  => $this->getCurrencyCode(),
            'PAYMENTREQUEST_0_ITEMAMT'       => (string) $this->getTotal(),
            'PAYMENTREQUEST_0_INVNUM'        => (string) $this->invoice,

            'RETURNURL' => Config::get('paypal.returnurl', ''),
            'CANCELURL' => Config::get('paypal.cancelurl', ''),
//            'BUTTONSOURCE' => Config::get('paypal.buttonsource', ''),
        ], $this->getItems());

        //Envia a requisição e obtém a resposta da PayPal
        $responseNvp = $this->sendNvpRequest($requestNvp, $this->sandbox);
        if (is_callable($callback)) {
            return $callback($responseNvp);
        };

        if (!isset($responseNvp['TOKEN'])) {
            throw new PaypalRequestException("Token don't exists.");
        }

        $query = [
            'cmd'   => '_express-checkout',
            'token' => $responseNvp['TOKEN'],
        ];

        $redirectURL = sprintf('%s?%s', $this->getPaypalUrl(), http_build_query($query));

        return $redirectURL;
    }

    /**
     * Vai para o checkout.
     *
     * @param  closure $callback
     */
    public function checkout($callback = null)
    {
        return $this->getCheckoutUrl(function ($response) use ($callback) {
            if (is_callable($callback)) {
                return $callback($response);
            };

            $query = [
                'cmd'   => '_express-checkout',
                'token' => $response['TOKEN'],
            ];

            $redirectURL = sprintf('%s?%s', $this->getPaypalUrl(), http_build_query($query));

            return redirect($redirectURL);
        });
    }

    /**
     * Seta o código da moeda.
     *
     * @param string $code ex: BRL, USD
     */
    public function setCurrencyCode($code)
    {
        $this->currencyCode = $code;

        return $this;
    }

    /**
     * Seta se é sandbox ou produção
     * Caso o valor não seja boleano
     * não faz nada.
     *
     * @param bool $sandbox
     */
    public function setSandbox($sandbox = true)
    {
        if (is_bool($sandbox)) {
            $this->sandbox = $sandbox;
        }

        return $this;
    }

    /**
     * Envia uma requisição NVP para uma API PayPal.
     *
     * @param array $requestNvp Define os campos da requisição.
     * @param bool $sandbox Define se a requisição será feita no sandbox ou no
     *                         ambiente de produção.
     *
     * @return array Campos retornados pela operação da API. O array de retorno poderá
     *               ser vazio, caso a operação não seja bem sucedida. Nesse caso, os
     *               logs de erro deverão ser verificados.
     */
    protected function sendNvpRequest(array $requestNvp, $sandbox = false)
    {
        //Endpoint da API
        $apiEndpoint = 'https://api-3t.' . ($sandbox ? 'sandbox.' : null);
        $apiEndpoint .= 'paypal.com/nvp';
        //Executando a operação
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $apiEndpoint);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->certificate);

        if ($this->certificate) {
            curl_setopt($curl, CURLOPT_SSLCERT, $this->certificateFile);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($requestNvp));

        $response = urldecode(curl_exec($curl));

        curl_close($curl);
        //Tratando a resposta
        $responseNvp = [];

        if (preg_match_all('/(?<name>[^\=]+)\=(?<value>[^&]+)&?/', $response, $matches)) {
            foreach ($matches['name'] as $offset => $name) {
                $responseNvp[$name] = $matches['value'][$offset];
            }
        }

        //Verificando se deu tudo certo e, caso algum erro tenha ocorrido,
        //gravamos um log para depuração.

        if (isset($responseNvp['ACK']) && $responseNvp['ACK'] != 'Success') {
            $errors = [];
            for ($i = 0;isset($responseNvp['L_ERRORCODE' . $i]); ++$i) {
                $message = sprintf("PayPal NVP %s[%d]: %s\n",
                    $responseNvp['L_SEVERITYCODE' . $i],
                    $responseNvp['L_ERRORCODE' . $i],
                    $responseNvp['L_LONGMESSAGE' . $i]);

                $errors[] = $message;
            }

            throw new PaypalRequestException(implode(',', $errors));
        }

        return $responseNvp;
    }

    /**
     * Seta o arquivo de certificado
     * Não é obrigatório.
     *
     * @param string $file
     */
    public function setCertificateFile($file)
    {
        $this->certificate     = true;
        $this->certificateFile = $file;

        return $this;
    }

    /**
     * Pega as credencias.
     *
     * @return array
     */
    protected function getCredentials()
    {
        //credenciais da API para o Sandbox
        //    $usernameSandbox = 'conta-business_api1.test.com';
        //    $passwordSandbox = '1365001380';
        //    $signatureSandbox = 'AiPC9BjkCyDFQXbSkoZcgqH3hpacA-p.YLGfQjc0EobtODs.fMJNajCx';
        $user      = $this->username;
        $pswd      = $this->password;
        $signature = $this->signature;

        if ($this->sandbox) {
            $user      = env('PAYPAL_SANDBOX_USERNAME', $user);
            $pswd      = env('PAYPAL_SANDBOX_PASSWORD', $pswd);
            $signature = env('PAYPAL_SANDBOX_SIGNATURE', $signature);
        }

        if (is_null($user) || is_null($pswd)) {
            throw new PaypalRequestException('No credentials');
        }

        if (is_null($signature) && !$this->certificate) {
            throw new PaypalRequestException('No credentials');
        }

        if (is_null($user) || is_null($pswd)) {
            throw new PaypalRequestException('No credentials');
        }

        if (is_null($signature) && !$this->certificate) {
            throw new PaypalRequestException('No credentials');
        }

        $requestNvp = [
            'USER' => $user,
            'PWD'  => $pswd,

            'VERSION'    => Config::get('paypal.version', '108.0'),
            'LOCALECODE' => $this->localeCode,
        ];

        $hdimg = Config::get('paypal.HDRIMG', false);
        if ($hdimg) {
            $requestNvp['HDRIMG'] = $hdimg;
        }

        if (!$this->certificate) {
            $requestNvp = array_merge(['SIGNATURE' => $signature], $requestNvp);
        }

        return $requestNvp;
    }

    /**
     * Pega dados da transação.
     *
     * @param  string $token
     *
     * @return array
     */
    public function getDatails($token)
    {
        $requestNvp = $this->getCredentials();
        $requestNvp = array_merge($requestNvp, [
            'METHOD' => 'GetExpressCheckoutDetails',
            'TOKEN'  => $token,
        ]);

        return $this->sendNvpRequest($requestNvp, $this->sandbox);
    }

    /**
     * Faz o do ExpresseChecoutPayment.
     *
     * @param  string $token
     *
     * @return array
     */
    public function doExpressCheckoutPayment($token)
    {
        $requestNvp = $this->getCredentials();
        $requestNvp = array_merge($requestNvp, $this->getDatails($token), [
            'METHOD'    => 'DoExpressCheckoutPayment',
            'NOTIFYURL' => Config::get('paypal.notifyurl', 'http://paypal.app/paypal/ipn'),
            'TOKEN'     => $token,
        ]);

        return $this->sendNvpRequest($requestNvp, $this->sandbox);
    }

    /**
     * Verifica se o pagamento está completo.
     *
     * @param  string $token
     *
     * @return bool
     */
    public function paymentCompleted($token)
    {
        $dtails = $this->getDatails($token);
        if (!isset($details['CHECKOUTSTATUS'])) {
            return false;
        }

        return ($details['CHECKOUTSTATUS'] == 'PaymentCompleted');
    }

    /**
     *   STARTDATE    Esse é o único campo obrigatório e especifica a data inicial. Qualquer transação cuja data for maior ou igual a especificada em STARTDATE será retornada pelo PayPal.
     *   ENDDATE Ao contrário da STARTDATE, esse campo é opcional e especifica a data final. Qualquer transação que estiver entre a data inicial e a data final (inclusive) serão retornadas pela operação TransactionSearch.
     *   EMAIL   O campo email é utilizado para pesquisar transações de um comprador específico, se informado, apenas as transações daquele comprador serão retornadas.
     *   RECEIVER    Assim como o campo EMAIL, esse campo recebe um email, porém, o email do vendedor. Esse campo não é muito útil em lojas que operam com apenas 1 vendedor, mas em market places, que operam com vários vendedores, esse campo pode ser extremamente útil.
     *   TRANSACTIONID   Sempre que uma transação é criada no PayPal, um identificador de transação é retornado para a aplicação. Para pesquisar uma transação específica, podemos informar o id dessa transação nesse campo.
     *   TRANSACTIONCLASS    Existem diversas classes de transações e podemos pesquisar por transações que estejam em uma classe específica:
     *   All – Vai retornar todas as transações, seria o mesmo que não enviar esse campo.
     *   Sent – Somente transações de pagamentos enviados serão retornadas.
     *   Received – Somente transações de pagamentos recebidos serão retornadas.
     *   Refund – Somente transações envolvendo estornos.
     *   AMT Esse campo permite uma pesquisa pelo valor da transação.
     *   CURRENCYCODE    Esse campo permite pesquisar as transações que foram feitas em uma determinada moeda (USD, BRL, etc.).
     *   STATUS  Permite uma pesquisa pelo status da transação:
     *   Pending – Vai retornar apenas as transações pendente de revisão.
     *   Processing – Vai retornar apenas as transações que estão em processamento.
     *   Success – Vai retornar apenas as transações bem sucedidas, ou seja, aquelas que o pagamento foi concluído e o dinheiro transferido para o vendedor.
     *
     * transaction Search.
     *
     * @param  array  $data
     *
     * @return array
     */
    public function transactionSearch(array $data)
    {
        if (!isset($data['STARTDATE'])) {
            $data['STARTDATE'] = '2014-02-03T00:00:00Z';
        }

        $requestNvp = $this->getCredentials();

        //Campos da requisição da operação TransactionSearch, como ilustrado acima.
        $requestNvp = array_merge($requestNvp, ['METHOD' => 'TransactionSearch'], $data);

        //Envia a requisição e obtém a resposta da PayPal
        $responseNvp = $this->sendNvpRequest($requestNvp, $this->sandbox);

        //Se a operação tiver sido bem sucedida, podemos listar as transações encontradas
        if (isset($responseNvp['ACK']) && $responseNvp['ACK'] == 'Success') {
            return $responseNvp;
        }

        throw new PaypalRequestException('No results');
    }

    /**
     * Seta o número da fatura/pedido.
     *
     * @param int|string $id
     */
    public function setInvoice($id)
    {
        $this->invoice = $id;

        return $this;
    }

    /**
     * Verifica se uma notificação IPN é válida, fazendo a autenticação
     * da mensagem segundo o protocolo de segurança do serviço.
     *
     * @param array $data Um array contendo a notificação recebida.
     *
     * @return bool TRUE se a notificação for autência, ou FALSE se
     *                 não for.
     */
    public function isIPNValid(array $data)
    {
        $endpoint = 'https://www.paypal.com';

        if (isset($data['test_ipn']) && $data['test_ipn'] == '1') {
            $endpoint = 'https://www.sandbox.paypal.com';
        }

        $endpoint .= '/cgi-bin/webscr?cmd=_notify-validate';

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($curl);
        $error    = curl_error($curl);
        $errno    = curl_errno($curl);

        curl_close($curl);

        $valid = empty($error) && $errno == 0 && $response == 'VERIFIED';

        if (!$valid) {
            return false;
        }

        //Se chegamos até aqui, significa que estamos lidando com uma
        //notificação IPN válida. Agora precisamos verificar se somos o
        //destinatário dessa notificação, verificando o campo receiver_email.
        return ($data['receiver_email'] == Config::get('paypal.email'));
    }

    /**
     * Define a linguagem local.
     *
     * @param string $code
     */
    public function setLocaleCode($code = 'pt_BR')
    {
        $this->localeCode = $code;

        return $this;
    }

    /**
     * Estorna a transação, parcial ou completa.
     *
     * @param  string $transactionId
     * @param  float $amount
     *
     * @return array
     */
    public function refundTransaction($transactionId, $note = null, $amount = null)
    {
        $requestNvp = $this->getCredentials();
        $type       = ['REFUNDTYPE' => 'Full'];
        if (!is_null($amount)) {
            $type = [
                'REFUNDTYPE'   => 'Partial',
                'AMT'          => str_replace(',', '.', str_replace('.', '', $amount)),
                'CURRENCYCODE' => $this->getCurrencyCode()];
        }

        $requestNvp = array_merge($requestNvp, [
            'METHOD' => 'RefundTransaction',

            'TRANSACTIONID' => $transactionId], $type);

        //Envia a requisição e obtém a resposta da PayPal
        if (!is_null($note)) {
            $requestNvp['NOTE'] = $note;
        }
        $responseNvp = $this->sendNvpRequest($requestNvp, $this->sandbox);

//        echo '<h3>A transação '.$transactionId.' foi estonada com sucesso. O transactionId do estorno é: '.$responseNvp['REFUNDTRANSACTIONID'].'</h3>';

        return $responseNvp;
    }

    /**
     * Estorna a transação por completa.
     *
     * @param  string $transactionId
     *
     * @return bool|string id da transação de estorno
     */
    public function refundFull($transactionId, $note = null)
    {
        $responseNvp = $this->refundTransaction($transactionId, $note);
        //Verifica se a operação foi bem sucedida
        if (isset($responseNvp['ACK']) && $responseNvp['ACK'] == 'Success') {
            return $responseNvp['REFUNDTRANSACTIONID'];
        }

        return false;
    }

    /**
     * Estorna um valor parcial da transação.
     *
     * @param  string $transactionId
     * @param  float $amount
     *
     * @return bool|string id da transação de estorno
     */
    public function refundPartial($transactionId, $amount, $note = null)
    {
        $responseNvp = $this->refundTransaction($transactionId, $note, $amount);
        //Verifica se a operação foi bem sucedida
        if (isset($responseNvp['ACK']) && $responseNvp['ACK'] == 'Success') {
            return $responseNvp['REFUNDTRANSACTIONID'];
        }

        return false;
    }
}
