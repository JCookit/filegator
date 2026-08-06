<template>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title">
        {{ lang('Select Folder') }}
      </p>
    </header>
    <section class="modal-card-body">
      <div class="tree">
        <ul class="tree-list">
          <TreeNode :node="$store.state.tree" @selected="select" />
        </ul>
      </div>
      <b-checkbox v-if="showHardlinkOption" v-model="hardlink">
        {{ lang('Hardlink') }}
      </b-checkbox>
    </section>
    <footer class="modal-card-foot">
      <button class="button" type="button" @click="$parent.close()">
        {{ lang('Close') }}
      </button>
    </footer>
  </div>
</template>

<script>
import TreeNode from './TreeNode'

export default {
  name: 'Tree',
  components: { TreeNode },
  props: {
    showHardlinkOption: {
      type: Boolean,
      default: false
    },
  },
  data() {
    return {
      hardlink: false,
    }
  },
  methods: {
    select(dir) {
      this.$emit('selected', {
        ...dir,
        hardlink: this.hardlink,
      })
      this.$parent.close()
    },
  },
}
</script>

<style>
.tree {
  min-height: 450px
}

.tree-list ul li {
  padding-left: 20px;
  margin: 6px 0;
}
</style>
