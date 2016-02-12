#Instalação:

Adicione no composer:

```
"require" : {
	"resultsystems/laravel-paypal": "dev-master"
},

```


#Uso em sandbox

Opcionalmente você pode criar em seu arquivo .env
As seguintes variaveis:
PAYPAL_SANDBOX_USERNAME=nome do usuário para sandbox
PAYPAL_SANDBOX_PASSWORD=senha do usuário para sandbox
PAYPAL_SANDBOX_SIGNATURE=assinatura para sandbox

##Class para uso em Sandbox

Você pode apenas instanciar a classe `PaypalSandboxRequestService` em vez da `PaypalRequestService` para uso em sandbox

#Métodos públicos

```
/**
 * Constrói a classe de requisição de pagamento.
 *
 * @param int|string       $invoice    número da fatura
 * @param string    $user       usuário
 * @param string    $pass       senha
 * @param string    $signature  assinatura
 */
__construct($invoice = null, $user = null, $pass = null, $signature = null)


/**
 * Adiciona itens.
 *
 * @param PaypalItem $item
 */
addItem(PaypalItem $item)

/**
 * Cria um item e adiciona ao array de itens.
 *
 * @param float     $quantity       Quantidade de produtos
 * @param string    $name           Nome do produto
 * @param string    $description    Descrição do produto
 * @param float     $amount         Valor do produto
 */
setItem($quantity, $name, $description, $amount)

/**
 * Pega a url para checkout
 * Se for passado uma closure
 * Executa ela informando o resultado para ela.
 *
 * @param  closure $callback url para redirecionar caso falhe
 *
 * @return closure
 */
getCheckoutUrl($callback = null)

/**
 * Vai para o checkout.
 *
 * @param  function $callback [description]
 */
checkout($callback = null)

/**
 * Seta o código da moeda.
 *
 * @param string $code ex: BRL, USD
 */
setCurrencyCode($code)

/**
 * Seta se é sandbox ou produção
 * Caso o valor não seja boleano
 * não faz nada.
 *
 * @param bool $sandbox
 */
setSandbox($sandbox = true)

/**
 * Seta o arquivo de certificado
 * Não é obrigatório.
 *
 * @param string $file
 */
setCertificateFile($file)

/**
 * Pega dados da transação.
 *
 * @param  string $token
 *
 * @return array
 */
getDatails($token)

/**
 * Verifica se o pagamento está completo.
 *
 * @param  string $token
 *
 * @return bool
 */
paymentCompleted($token)

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
transactionSearch(array $data)

/**
 * Seta o número da fatura/pedido.
 *
 * @param int|string $id
 */
setInvoice($id)
```

#Exemplos de uso

```
/**
 * Faz nova requisição
 * informando o número da fatura 15
 * usuario, senha e assinatura serão pegos das configurações
 */
$paypal = new \ResultSystems\Paypal\Services\PaypalRequestService(15);

/*($quantity, $name, $description, $amount, $item_amount = null)*/
$paypal->addItem(new \ResultSystems\Paypal\PaypalItem('1.0', 'teste', 'xxx asdkflj daljkdfaslçkfj asdçlkfjsdalçfsd', '150'))
    ->setItem('1', 'teste 2', 'xxx 2', '39');
```

```
/*Vai para o checkout direto se passar*/
return $paypal->checkout();
```

```
/**
* Executa a closure
* após o processo de verificar os items
* cabendo a closure redirecionar e fazer as demais coisas
*/
return $paypal->checkout(function ($response) {
    dd($response);
});

return $paypal->getCheckoutUrl(function ($response) {
    dd($response);
});
```

```
/**
 * Retorna a url para redirecionamento
 */
dd($paypal->getCheckoutUrl());
```

```
/**
* Faz nova requisição
* informando o número da fatura 15
* passando usuario, senha e assintura
*/
$paypal = new \ResultSystems\Paypal\Services\PaypalRequestService(15, 'usuario', 'senha', 'assinatura');
```

```
/**
* Faz nova requisição
* informando o número da fatura 15
* passando usuario e senha não informa a assinatura
* pois vamos usar certificado
*/
$paypal = new \ResultSystems\Paypal\Services\PaypalRequestService(15, 'usuario', 'senha');
$paypla->setCertificateFile('certificado.txt');
```

```
/**
* Faz nova requisição
* informando o número da fatura 15
* usuario, senha e assinatura serão pegos das configurações
*/
$paypal = new \ResultSystems\Paypal\Services\PaypalRequestService(15);

/*($quantity, $name, $description, $amount, $item_amount = null)*/
$paypal->setSandbox()->addItem(new \ResultSystems\Paypal\PaypalItem('1.0', 'teste', 'xxx asdkflj daljkdfaslçkfj asdçlkfjsdalçfsd', '150'))
    ->addItem(new \ResultSystems\Paypal\PaypalItem('1', 'teste 2', 'xxx 2', '39'));

/*Vai para o checkout direto*/
return $paypal->checkout();
```

```
/**
 * Faz nova requisição
 * informando o número da fatura 15
 * passando usuario, senha e assintura
 */
$paypal = new \ResultSystems\Paypal\Services\PaypalRequestService(15, 'usuario', 'senha', 'assinatura');
```

```
/**
 * Faz nova requisição
 * informando o número da fatura 15
 * passando usuario e senha não informa a assinatura
 * pois vamos usar certificado
 */
$paypal = new \ResultSystems\Paypal\Services\PaypalRequestService(15, 'usuario', 'senha');
$paypla->setCertificateFile('certificado.txt');
```

#Estorno total
```
    $paypal = new \ResultSystems\Paypal\Services\PaypalRequestService;
    $transactionId=$_POST["transactionId"];
    $note="Motivo de devolução do valor opcional aqui";
    try
    {
        $id=$paypal->refundFull($transactionId, $note);
        echo 'transação estornada com sucesso!, id estorno: '.$id;
    } catch(\ResultSystems\Paypal\Exceptions\PaypalRequestException $e){
        echo 'transação não foi estornada, houve uma falha: '.$e->getMessage();
    }
```

#Estorno parcial
```
    $paypal = new \ResultSystems\Paypal\Services\PaypalRequestService;
    $transactionId=$_POST["transactionId"];
    $amount=$_POST['amount'];
    $note="Motivo de devolução do valor opcional aqui";
    try
    {
        $id=$paypal->refundPartial($transactionId, $note, $amount);
        echo 'transação estornada com sucesso!, id estorno: '.$id;
    } catch(\ResultSystems\Paypal\Exceptions\PaypalRequestException $e){
        echo 'transação não foi estornada, houve uma falha: '.$e->getMessage();
    }
```
