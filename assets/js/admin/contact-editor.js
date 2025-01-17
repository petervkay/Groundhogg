(function ($, editor) {

  $.extend(editor, {

    init: function () {

      $('#meta-table').click(function (e) {
        if ($(e.target).closest('.deletemeta').length) {
          $(e.target).closest('tr').remove()
        }
      })

      $('.addmeta').click(function () {

        var $newMeta = '<tr>' +
          '<th>' +
          '<input type=\'text\' class=\'input\' name=\'newmetakey[]\' placeholder=\'' + $('.metakeyplaceholder').text() + '\'>' +
          '</th>' +
          '<td>' +
          '<input type=\'text\' class=\'regular-text\' name=\'newmetavalue[]\' placeholder=\'' + $('.metavalueplaceholder').text() + '\'>' +
          ' <span class="row-actions"><span class="delete"><a style="text-decoration: none" href="javascript:void(0)" class="deletemeta"><span class="dashicons dashicons-trash"></span></a></span></span>\n' +
          '</td>' +
          '</tr>'
        $('#meta-table').find('tbody').prepend($newMeta)

      })

      $('.create-user-account').click(function () {
        $('#create-user-form').submit()
      })

      $('.nav-tab').click(function (e) {

        var $tab = $(this)

        $('.nav-tab').removeClass('nav-tab-active')
        $tab.addClass('nav-tab-active')

        $('.tab-content-wrapper').addClass('hidden')
        $('#' + $tab.attr('id') + '_content').removeClass('hidden')

        $('#active-tab').val($tab.attr('id').replace('tab_', ''))
        document.cookie = 'gh_contact_tab=' + $tab.attr('id') + ';path=/;'

      })

      $(document).on('click', '.edit-notes', function (e) {
        var $note = get_note(e.target)
        $note.find('.gh-note-view').hide()
        $note.find(' .edited-note-text').height($note.find('.gh-note-view').height())
        $note.find('.gh-note-edit').show()
      })

      $(document).on('click', '.cancel-note-edit', function (e) {
        var $note = get_note(e.target)
        $note.find('.gh-note-edit').hide()
        $note.find('.gh-note-view').show()
      })

      $(document).on('click', '.save-note', function (e) {
        var $note = get_note(e.target)
        var note_id = $note.attr('id')
        save_note(note_id)
      })

      $(document).on('click', '.delete-note', function (e) {
        var $note = get_note(e.target)
        var note_id = $note.attr('id')
        delete_note(note_id)
      })

      $('#add-note').click(function (event) {
        add_note()
      })

      $('.contact-info-cards .meta-box-sortables').sortable({
        placeholder: 'sortable-placeholder',
        // connectWith: '.ui-sortable',
        handle: '.hndle',
        // axis: 'y',
        start: function (e, ui) {
          ui.helper.css('left',
            (ui.item.parent().width() - ui.item.width()) / 2)
          ui.placeholder.height(ui.item.height())
          ui.placeholder.width(ui.item.width())
        },
        stop: saveInfoCardOrder
      })

      $(document).on('click', '.contact-info-cards button.handlediv', function (e) {
        $(this).closest('.info-card').toggleClass('closed')
        saveInfoCardOrder()
      })

      $(document).on('click', '.contact-info-cards button.handle-order-higher', function (e) {
        $(this).closest('.info-card').insertBefore($(this).closest('.info-card').prev())
        saveInfoCardOrder()
      })

      $(document).on('click', '.contact-info-cards button.handle-order-lower', function (e) {
        $(this).closest('.info-card').insertAfter($(this).closest('.info-card').next())
        saveInfoCardOrder()
      })

      $(document).on('click', '.expand-all', function (e) {
        $('.info-card').removeClass('closed')
        saveInfoCardOrder()
      })

      $(document).on('click', '.collapse-all', function (e) {
        $('.info-card').addClass('closed')
        saveInfoCardOrder()
      })

      $(document).on('click', '.view-cards', function (e) {
        $('.info-card-views').toggleClass('hidden')
      })

      $(document).on('change', '.hide-card', function (e) {
        var $checkbox = $(this)
        if ($checkbox.is(':checked')) {
          $('.info-card#' + $checkbox.val()).removeClass('hidden')
        } else {
          $('.info-card#' + $checkbox.val()).addClass('hidden')
        }

        saveInfoCardOrder()
      })
    }
  })

  /**
   * Add a new note
   */
  function add_note () {

    var $newNote = $('#add-new-note')
    var $notes = $('#gh-notes')

    adminAjaxRequest(
      {
        action: 'groundhogg_add_notes',
        note: $newNote.val(),
        contact: editor.contact_id
      },
      function callback (response) {
        // Handler
        if (response.success) {
          $newNote.val('')
          $notes.prepend(response.data.note)
        } else {
          alert(response.data)
        }
      }
    )
  }

  /**
   * Add a new note
   */
  function saveInfoCardOrder () {

    var $cards = $('.contact-info-cards .info-card')
    var cardOrder = []
    $cards.each(function (i, card) {
      cardOrder.push({
        id: card.id,
        open: !$(card).hasClass('closed'),
        hidden: $(card).hasClass('hidden'),
      })
    })

    // console.log(cardOrder)

    adminAjaxRequest(
      {
        action: 'groundhogg_save_card_order',
        cardOrder: cardOrder
      }
    )
  }

  /**
   * Save the edited note...
   *
   * @param note_id
   */
  function save_note (note_id) {

    var $note = $('#' + note_id)
    var new_note_text = $note.find('.edited-note-text').val()
    showSpinner()

    adminAjaxRequest(
      {
        action: 'groundhogg_edit_notes',
        note: new_note_text,
        note_id: note_id
      },
      function callback (response) {
        // Handler
        hideSpinner()
        if (response.success) {
          $note.replaceWith(response.data.note)
        } else {
          alert(response.data)
        }
      }
    )
  }

  /**
   * Delete a note
   *
   * @param note_id
   */
  function delete_note (note_id) {

    if (!confirm(editor.delete_note_text)) {
      return
    }

    var $note = $('#' + note_id)

    adminAjaxRequest(
      {
        action: 'groundhogg_delete_notes',
        note_id: note_id
      },
      function callback (response) {
        // Handler
        if (response.success) {
          $note.remove()
        } else {
          alert(response.data)
        }
      }
    )
  }

  /**
   *
   * Get the note
   *
   * @param e
   * @returns {any | Element | jQuery}
   */
  function get_note (e) {
    return $(e).closest('.gh-note')
  }

  $(function () {
    editor.init()
  })

})(jQuery, ContactEditor)