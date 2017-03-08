<?php

/**
 * pt_PT
 *
 * PT Portuguese message token translations for the core sprinkle.
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author Bruno Silva (brunomnsilva@gmail.com)
 */

return [
    "ERROR" => [
        "@TRANSLATION" => "Erro",

        "TITLE" => "Perturbância na Força",
        "DESCRIPTION" => "Sentimos uma grande perturbância na Força.",
        "ENCOUNTERED" => "Uhhh...algo aconteceu.  Não sabemos bem o quê.",
        "DETAIL" => "Eis o que sabemos:",
        "RETURN" => 'Clique <a href="{{url}}">aqui</a> para regressar à página inicial.',

        "SERVER" => "Oops, parece que o nosso servidor deu o berro. Se é um administrador, por favor consulte o log de erros PHP ou UF.",

        "400" => [
            "TITLE" => "Erro 400: Pedido Inválido",
            "DESCRIPTION" => "Provavelmente a culpa não é sua.",
        ],

        "404" => [
            "TITLE" => "Erro 404: Página não Encontrada",
            "DESCRIPTION" => "Parece que não conseguimos encontrar a página que procura.",
            "DETAIL" => "Tentámos encontrar a sua página...",
            "EXPLAIN" => "Não conseguimos encontrar a página que procura.",
            "RETURN" => 'De qualquer forma, clique <a href="{{url}}">aqui</a> para regressar à página inicial.'
        ],

        "CONFIG" => [
            "TITLE" => "Problema de Configuração do UserFrosting!",
            "DESCRIPTION" => "Alguns requisitos de configuração do UserFrosting não foram satisfeitos.",
            "DETAIL" => "Algo não está bem.",
            "RETURN" => 'Por favor corrija os seguintes erros, depois <a href="{{url}}">refresque</a> a página.'
        ]
    ]
];
