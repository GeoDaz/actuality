// modules
import React, { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Link } from 'react-router-dom';
import Slider from 'react-slick';
import { Button } from 'semantic-ui-react';
import { formatDate } from '../../../functions';
// components
import PageWrapper from '../../PageWrapper';
import GoBackButton from '../../GoBackButton';
import useFetch from '../../../hooks/useFetch';
import { DELETE } from '../../../constants/methods';
import { useHistory } from 'react-router-dom';
import { addMessage } from '../../../reducers/messages';
import ScrollReveal from '../../ScrollReveal';
import Tag from '../../elements/Tag';
import CommentArea from '../../CommentArea';
import SectionAds from '../../sections/SectionAds';

const defaultGoBack = '/entity/actualities/';
const ActualityArticle = ({ result }) => {
	const history = useHistory();
	const dispatch = useDispatch();
	const user = useSelector(state => state.user);
	const [actuality, setActuality] = useState((result && result.actuality) || null);
	const [resultDelete, loadDelete, loadingDelete] = useFetch();

	useEffect(() => {
		if (result && result.success) {
			setActuality(result.actuality);
		}
	}, [result]);

	useEffect(() => {
		if (result.pokemon && result.pokemon.id) {
			dispatch(addMessage(resultDelete.message, true));
			history.replace(defaultGoBack);
		}
	}, [resultDelete]);

	const handleDelete = e =>
		loadDelete({ url: `actualities/${actuality.id}`, method: DELETE });

	if (!actuality || !actuality.id) return null;
	return (
		<PageWrapper
			title={actuality.title}
			className="actuality article"
			metadescription={actuality.shortDescription}
			metaimage={
				actuality.images.length > 0 && `actualities/${actuality.images[0]}`
			}
		>
			<div className="mb-3">
				<GoBackButton defaultUrl="/entity/actualities" />
				{user.isAdmin && (
					<>
						<Button
							as={Link}
							to={`/entity/actualities/${actuality.id}/update`}
							color="blue"
							content="Modifier"
							icon="pencil"
							className="mr-2"
						/>
						<Button
							loading={loadingDelete}
							onClick={handleDelete}
							color="red"
							content="Supprimer"
							icon="trash alternate"
						/>
					</>
				)}
			</div>
			<ScrollReveal animation="zoomIn" earlier>
				{actuality.images.length > 0 && (
					<div className="slick-wrapper">
						<Slider
							infinite
							dots
							speed={500}
							slidesToShow={actuality.images.length > 1 ? 2 : 1}
							slidesToScroll={1}
							responsive={[
								{
									breakpoint: 576,
									settings: { slidesToShow: 1 },
								},
							]}
						>
							{actuality.images.map((path, i) => (
								<div key={i} className="image mb-2">
									<img
										src={`/images/actualities/${path}`}
										className="img-fluid"
										alt="Actualité"
										// TODO gerer une taille fixe
									/>
								</div>
							))}
						</Slider>
					</div>
				)}
			</ScrollReveal>
			<ScrollReveal animation="zoomIn" earlier>
				{actuality.tags.length > 0 && (
					<div className="text-center">
						{actuality.tags.map((tag, i) => (
							<Tag key={i} tag={tag} />
						))}
					</div>
				)}
				<p className="date">
				</p>
				<div
					className="description framed wysiwyg-result"
					dangerouslySetInnerHTML={{
						__html: actuality.parsedDescription || actuality.shortDescription,
					}}
				/>
			</ScrollReveal>
			<SectionAds />
			<CommentArea entity={actuality} entityName="actuality" />
		</PageWrapper>
	);
};
export default ActualityArticle;
