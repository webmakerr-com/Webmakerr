<template>
    <div class="fct-integration-container fct-layout-width">
        <PageHeading :title="$t('Integrations & Addons')"/>

        <div style="margin-top: 100px;" class="fct-integration-content">
            <CardContainer>
                <CardBody>
                    <div class="flex flex-col items-center text-center gap-4 py-16">
                        <IconButton circle size="large">
                            <DynamicIcon name="CustomerService2Line"/>
                        </IconButton>
                        <h2 class="fct-card-header-title mb-1">
                            {{ $t('Need help or customisation?') }}
                        </h2>
                        <p class="card-header-text mt-0 max-w-2xl">
                            {{ $t('We are here to help tailor your experience with Webmakerr. Let us know what you need and our team will reach out.') }}
                        </p>
                        <el-button type="primary" size="large" @click="goToContact">
                            {{ $t('Contact Now') }}
                        </el-button>
                    </div>
                </CardBody>
            </CardContainer>
        </div>
    </div>
</template>


<script type="text/babel">
import {Container as CardContainer, Body as CardBody} from '@/Bits/Components/Card/Card.js';
import PageHeading from "@/Bits/Components/Layout/PageHeading.vue";
import IconButton from "@/Bits/Components/Buttons/IconButton.vue";
import DynamicIcon from "@/Bits/Components/Icons/DynamicIcon.vue";

export default {
    name: "Integrations",
    components: {
        CardBody,
        CardContainer,
        PageHeading,
        IconButton,
        DynamicIcon
    },
    methods: {
        goToContact() {
            window.open('https://webmakerr.com/contact-us', '_blank');
        }
    }
};
</script>
