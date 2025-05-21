<button x-on:click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode); 
                    document.documentElement.classList.toggle('dark')"
        class="fixed bottom-4 right-4 w-12 h-12 flex items-center justify-center rounded-full shadow-md transition-all duration-500 transform"
        x-bind:class="darkMode ? 'bg-gray-200 text-black' : 'bg-gray-800 text-white'">

    <span x-show="!darkMode" x-transition:enter="transition duration-500 ease-in-out transform"
          x-transition:enter-start="opacity-0 scale-75"
          x-transition:enter-end="opacity-100 scale-100"
          x-transition:leave="transition duration-500 ease-in-out transform"
          x-transition:leave-start="opacity-100 scale-100"
          x-transition:leave-end="opacity-0 scale-75"
          class="absolute">
        🌙
    </span>

    <span x-show="darkMode" x-transition:enter="transition duration-500 ease-in-out transform"
          x-transition:enter-start="opacity-0 scale-75"
          x-transition:enter-end="opacity-100 scale-100"
          x-transition:leave="transition duration-500 ease-in-out transform"
          x-transition:leave-start="opacity-100 scale-100"
          x-transition:leave-end="opacity-0 scale-75"
          class="absolute">
        ☀️
    </span>

</button>
