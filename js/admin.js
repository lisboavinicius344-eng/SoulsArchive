// Solicita confirmação em uma janela própria antes de enviar formulários de exclusão.
function criarJanelaConfirmacao() {
  var janela = document.createElement('dialog');
  janela.className = 'janela-confirmacao';

  var titulo = document.createElement('h2');
  var detalhe = document.createElement('p');

  var acoes = document.createElement('div');
  acoes.className = 'janela-acoes';

  var cancelar = document.createElement('button');
  cancelar.type = 'button';
  cancelar.className = 'btn-contorno';
  cancelar.textContent = 'Cancelar';

  var confirmar = document.createElement('button');
  confirmar.type = 'button';
  confirmar.className = 'btn-excluir';
  confirmar.textContent = 'Excluir';

  acoes.append(cancelar, confirmar);
  janela.append(titulo, detalhe, acoes);
  document.body.append(janela);

  cancelar.addEventListener('click', function () {
    janela.close();
  });

  return { janela: janela, titulo: titulo, detalhe: detalhe, confirmar: confirmar };
}

document.querySelectorAll('form[data-confirmar]').forEach(function (formulario) {
  formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();

    if (typeof HTMLDialogElement !== 'function') {
      if (window.confirm(formulario.dataset.confirmar)) {
        formulario.submit();
      }
      return;
    }

    var confirmacao = criarJanelaConfirmacao();
    confirmacao.titulo.textContent = formulario.dataset.confirmar;
    confirmacao.detalhe.textContent = formulario.dataset.confirmarDetalhe || '';

    confirmacao.confirmar.addEventListener('click', function () {
      formulario.submit();
    });

    confirmacao.janela.addEventListener('close', function () {
      confirmacao.janela.remove();
    });

    confirmacao.janela.showModal();
  });
});
