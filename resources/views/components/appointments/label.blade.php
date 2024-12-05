<div class="flex items-center">
  <div class="w-2 h-2 mr-2 rounded-full {{ $appointment->color_classes['bg'] }}">
  </div>

  <span class="{{ $appointment->color_classes['text'] }}">
    {{ $appointment->subject }}
  </span>
</div>
