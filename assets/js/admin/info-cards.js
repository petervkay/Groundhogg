(function ($) {

  $(document).on('click', '.ic-section-header', function () {
    $(this).closest('.ic-section').toggleClass('open');
  });

  $(document).on('tinymce-editor-setup', function (event, editor) {
    editor.settings.toolbar1 = 'bold,italic,underline,blockquote,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,undo,redo,link' //Teeny -fullscreen
    editor.settings.height = 200
    editor.on('click', function (ed, e) {
      $(document).trigger('to_mce')
    });
  })

  function renderEmailEditor () {

    setTimeout(function () {

      wp.editor.initialize(
        'email_content',
        {
          tinymce: true,
          // quicktags: true
        }
      )
    }, 50)
  }

  function destroyEmailEditor () {
    wp.editor.remove(
      'email_content'
    )
  }

  $(function () {
    renderEmailEditor()

    $(document).on('GroundhoggModalContentPulled', destroyEmailEditor)
    $(document).on('GroundhoggModalContentPulled', renderEmailEditor)
    $(document).on('GroundhoggModalContentPushed', destroyEmailEditor)
    $(document).on('GroundhoggModalContentPushed', renderEmailEditor)
  })

})(jQuery)
