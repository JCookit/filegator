import { shallowMount } from '@vue/test-utils'
import axios from 'axios'
import TransferStatus from '../../../frontend/views/partials/TransferStatus.vue'

jest.mock('axios')

describe('OneDrive status', () => {
  let wrapper
  beforeEach(() => {
    jest.useFakeTimers()
    axios.get.mockResolvedValue({ data: { data: { enabled: true, available: true, uploading: 0, queued: 0, transfers: [] } } })
    wrapper = shallowMount(TransferStatus, {
      stubs: ['b-icon'],
      mocks: { formatBytes: value => `${value || 0} B` },
    })
  })
  afterEach(() => {
    wrapper.destroy()
    jest.clearAllTimers()
  })
  it('reports an empty queue without implying every source file was copied', async () => {
    await wrapper.vm.$nextTick()
    expect(wrapper.text()).toContain('no pending uploads')
  })
  it('shows active transfers and queue counts', async () => {
    await wrapper.vm.$nextTick()
    wrapper.setData({ status: { enabled: true, available: true, uploading: 1, queued: 2, transfers: [{ name: 'episode.mkv', bytes: 5, size: 10 }] } })
    await wrapper.vm.$nextTick()
    expect(wrapper.text()).toContain('1 uploading, 2 queued')
    expect(wrapper.find('progress').element.value).toBe(5)
  })
  it('does not keep displaying idle when polling fails', async () => {
    await wrapper.vm.$nextTick()
    axios.get.mockRejectedValue(new Error('offline'))
    await wrapper.vm.poll()
    expect(wrapper.vm.summary).toBe('status unavailable')
  })
})
