import '../css/app.scss'

const $ = require('jquery')
require('bootstrap')
require('typeface-open-sans')

const zipcodes = require('./ressources/zipcodes.ch.json')

// attach jquery to window
window.$ = $

// register some basic usability functionality
$(document).ready(() => {
  // give instant feedback on form submission
  $('form').on('submit', function (e) {
    const $form = $(this)
    const $buttons = $('.btn', $form)
    if (!$buttons.hasClass('no-disable')) {
      $buttons.addClass('disabled')
      $buttons.attr('disabled', 'disabled')
    }
  })

  $('[data-toggle="popover"]').popover()

  // force reload on user browser button navigation
  $(window).on('popstate', () => {
    window.location.reload(true)
  })

  const postalCodeField = $('input', '.postal-code-input')
  if (postalCodeField.length) {
    const localityField = $('input', '.locality-input')
    const cantonField = $('input', '.canton-input')

    if (!localityField.val()) {
      localityField.prop('disabled', true)
    }
    if (!cantonField.val()) {
      cantonField.prop('disabled', true)
    }

    const keyUpHandler = function () {
      const postalCode = postalCodeField.val()
      if (postalCode.length === 4) {
        const result = zipcodes.find(z => z.postalCode === postalCode)
        if (result) {
          localityField.val(result.locality)
          cantonField.val(result.canton)
        }

        localityField.prop('disabled', false)
        cantonField.prop('disabled', false)
      }
    }

    keyUpHandler()
    postalCodeField.on('keyup', keyUpHandler)
  }
})
