###
#    Copyright (c) ppy Pty Ltd <contact@ppy.sh>.
#
#    This file is part of osu!web. osu!web is distributed with the hope of
#    attracting more community contributions to the core ecosystem of osu!.
#
#    osu!web is free software: you can redistribute it and/or modify
#    it under the terms of the Affero GNU General Public License version 3
#    as published by the Free Software Foundation.
#
#    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
#    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
#    See the GNU Affero General Public License for more details.
#
#    You should have received a copy of the GNU Affero General Public License
#    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
###

class @UserCard
  triggerDelay: 200
  fadeLength: 220

  constructor: ->
    $(document).on 'mouseover', '.js-usercard', @onMouseOver


  onMouseOver: (event) =>
    el = event.currentTarget
    userId = el.dataset.userId
    return unless userId
    return if _.find(currentUser.blocks, target_id: parseInt(userId)) # don't show cards for blocked users
    return if @recycle(el)

    # wrong userId, destroy current tooltip
    if el._tooltip? && el._tooltip != el.dataset.userId
      $(el).qtip('api').destroy()

    el._tooltip = userId

    at = el.getAttribute('data-tooltip-position') ? 'right center'
    my = switch at
      when 'top center' then 'bottom center'
      when 'left center' then 'right center'
      when 'right center' then 'left center'

    # react should override the existing content after mounting
    card = $('#js-usercard__loading-template').children().clone()[0]
    card.classList.replace 'js-react--user-card', 'js-react--user-card-tooltip'
    delete card.dataset.reactTurbolinksLoaded
    card.dataset.lookup = userId

    options =
      events:
        render: reactTurbolinks.boot
      style:
        classes: 'qtip--user-card'
        def: false
        tip: false
      content:
        text: card
      position:
        at: at
        my: my
        viewport: $(window)
      show:
        delay: @triggerDelay
        ready: true
        effect: -> $(this).fadeTo(110, 1)
      hide:
        fixed: true
        delay: @triggerDelay
        effect: -> $(this).fadeTo(110, 0)

    $(el).qtip options


  recycle: (el) ->
    # disable usercards on mobile
    if osu.isMobile()
      # disable existing cards when entering 'mobile' mode
      if el._tooltip?
        event.preventDefault()
        $(el).qtip('api').disable()

      return true

    # when qtip has already been init for current element
    if el._tooltip? && el._tooltip == el.dataset.userId
      api = $(el).qtip('api')
      if api.disabled
        api.enable()
        $(el).trigger('mouseover')

      return true
