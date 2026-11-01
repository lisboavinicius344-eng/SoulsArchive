      <div class="form-group campo-largo">

        <label for="arquivo_imagem">Imagem</label>

        <?php if ($imagemAtual !== '' && imagemValida($imagemAtual)): ?>
          <img src="<?= e(urlImagem($imagemAtual)) ?>" alt="Imagem atual" class="imagem-atual">

          <label class="campo-marcar">
            <input type="checkbox" name="remover_imagem" value="1">
            Remover a imagem atual
          </label>
        <?php endif; ?>

        <input type="file" id="arquivo_imagem" name="arquivo_imagem" accept="image/jpeg,image/png,image/webp,image/gif">
        <small>JPG, PNG, WEBP ou GIF, com até 5 MB.</small>

        <details class="opcao-url" <?= $imagemAtual !== '' && !imagemValida($imagemAtual) ? 'open' : '' ?>>
          <summary>Usar uma URL em vez de enviar um arquivo</summary>
          <label for="imagem">URL http(s) ou caminho dentro de img/</label>
          <input type="text" id="imagem" name="imagem" value="<?= e($imagemAtual) ?>" maxlength="255" placeholder="https://...">
        </details>

      </div>
