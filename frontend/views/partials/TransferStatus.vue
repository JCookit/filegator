<template>
  <details v-if="status.enabled" class="transfer-status">
    <summary>
      <b-icon icon="cloud-upload-alt" size="is-small" />
      <span>OneDrive: {{ summary }}</span>
    </summary>
    <div class="transfer-details" aria-live="polite">
      <p v-if="!status.available">
        Transfer status unavailable
      </p>
      <template v-else>
        <p>{{ status.uploading }} uploading / {{ status.queued }} queued / {{ formatBytes(status.speed) }}/s</p>
        <p v-if="status.errors">
          {{ status.errors }} transfer errors since service start
        </p>
        <p v-if="status.diskError">
          Upload cache needs attention
        </p>
        <p v-if="status.retrying">
          {{ status.retrying }} files retrying
        </p>
        <div v-for="file in status.transfers" :key="file.name" class="transfer-file">
          <span>{{ file.name }}</span>
          <progress :value="file.bytes" :max="file.size || 1" />
          <small>{{ formatBytes(file.bytes) }} / {{ formatBytes(file.size) }}</small>
        </div>
        <p v-if="status.queued">
          Waiting: {{ status.queued }} files
        </p>
        <small>Updated {{ new Date(status.updated * 1000).toLocaleTimeString() }}</small>
      </template>
    </div>
  </details>
</template>

<script>
import axios from 'axios'

export default {
  name: 'TransferStatus',
  data() {
    return { status: { enabled: false }, timer: null, stopped: false }
  },
  computed: {
    summary() {
      if (!this.status.available) return 'status unavailable'
      if (this.status.diskError || this.status.retrying) return 'needs attention'
      if (this.status.uploading) return `${this.status.uploading} uploading, ${this.status.queued} queued`
      if (this.status.queued) return `${this.status.queued} queued`
      return 'no pending uploads'
    },
  },
  mounted() {
    this.poll()
  },
  beforeDestroy() {
    this.stopped = true
    clearTimeout(this.timer)
  },
  methods: {
    async poll() {
      try {
        const response = await axios.get('transferstatus', { timeout: 8000 })
        if (!this.stopped) this.status = response.data.data
      } catch (error) {
        if (!this.stopped) this.status = { ...this.status, available: false }
      } finally {
        if (!this.stopped) this.timer = setTimeout(() => this.poll(), 5000)
      }
    },
  },
}
</script>

<style scoped>
.transfer-status { margin: 0 0 12px; border-bottom: 1px solid #ddd; padding: 8px 0; position: relative; z-index: 1001; background: white; }
summary { cursor: pointer; overflow-wrap: anywhere; }
.transfer-details { padding: 8px 0; max-height: 320px; overflow: auto; }
.transfer-file { display: grid; gap: 4px; margin: 10px 0; overflow-wrap: anywhere; }
progress { width: 100%; height: 8px; }
</style>
